<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\QuizzRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;
use App\Repository\QuestionRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Psr\Cache\CacheItemPoolInterface;

// Importez les entités nécessaires
use App\Entity\User;
use App\Entity\Quizz;
use App\Entity\Question;
use App\Entity\Token;

class ApiController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private CsrfTokenManagerInterface $csrfTokenManager;


    public function __construct(CsrfTokenManagerInterface $csrfTokenManager, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->csrfTokenManager = $csrfTokenManager;

    }

    #[Route('/api/quizz', name: 'create_quizz', methods: ['POST'])]
    public function create(Request $request,CacheItemPoolInterface  $cache): JsonResponse
    {
        // Récupérer les données de la requête
    
        $requestData = json_decode($request->getContent(), true);
        
        // Récupérer l'ID de l'utilisateur depuis les données de la requête
        $userId = $requestData['user_id'] ?? null;
        
        // Vérifier si l'ID de l'utilisateur est fourni
        if (!$userId) {
            return $this->json(['message' => 'L\'ID de l\'utilisateur est manquant'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier le jeton CSRF
        self::tcheckCSRF($requestData['action'],$requestData['csrf_token'],$cache);


        // Trouver l'utilisateur correspondant à l'ID
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        
        // Vérifier si l'utilisateur existe
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }
        
        // Désérialiser les données de la requête en un objet Quizz
        $quizzData = $requestData['title'] ?? null;
        if (!$quizzData) {
            return $this->json(['message' => 'Les données du quizz sont manquantes'], Response::HTTP_BAD_REQUEST);
        }
    
        $quizz = new Quizz();
        $quizz->setTitle($quizzData);
        $quizz->setUser($user);
    
        $questionsData = $requestData['question'] ?? [];
        foreach ($questionsData as $questionData) {
            $question = new Question();
            $question->setTitle($questionData['title'] ?? '');
            $question->setoptionA($questionData['options']['a'] ?? '');
            $question->setOptionB($questionData['options']['b'] ?? '');
            $question->setOptionC($questionData['options']['c'] ?? '');
            $question->setOptionD($questionData['options']['d'] ?? '');
            $question->setCorrectOption($questionData['correct_option'] ?? '');
    
            $quizz->addQuestions($question);
        }
    
        // Persister le quiz dans la base de données
        $this->entityManager->persist($quizz);
        $this->entityManager->flush();
    
        // Retourner une réponse JSON avec le quiz créé
        return $this->json($quizz, Response::HTTP_CREATED, [], ['groups' => 'quizz']);
    }
        
    

    #[Route('/api/quizz/{id}', name: 'update_quizz', methods: ['PUT'])]
    public function update(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, Quizz $quizz): JsonResponse {

        try {
            // Étape 1: Désérialiser pour mettre à jour le quizz principal
            $serializer->deserialize($request->getContent(), Quizz::class, 'json', ['object_to_populate' => $quizz]);
            
            // Étape 2: Récupérer toutes les questions actuelles du quizz
            $currentQuestions = $quizz->getQuestions()->toArray();
            
            // Étape 3: Traiter les questions envoyées dans la requête
            $data = json_decode($request->getContent(), true);
            $newOrUpdatedQuestions = [];
            
            foreach ($data['questions'] as $questionData) {
                if (isset($questionData['id'])) {
                    // Rechercher la question dans les questions actuelles du quizz
                    $question = $entityManager->getRepository(Question::class)->find($questionData['id']);
                } else {
                    // Créer une nouvelle question
                    $question = new Question();
                }
                // Mettre à jour les propriétés de la question
                $question->setTitle($questionData['title']); // Utiliser la clé "title" pour définir le titre
                $question->setOptionA($questionData['options']['a'] ?? '');
                $question->setOptionB($questionData['options']['b']?? '');
                $question->setOptionC($questionData['options']['c']?? '');
                $question->setOptionD($questionData['options']['d']?? '');
                $question->setCorrectOption($questionData['correct_option']);
    
                
                // Associer la question au quizz
                $quizz->addQuestions($question);
                
                // Ajouter la question à la liste des nouvelles ou mises à jour
                $newOrUpdatedQuestions[] = $question->getId();
            }
            
            // Étape 4: Supprimer les questions qui n'existent plus
            foreach ($currentQuestions as $question) {
                if (!in_array($question->getId(), $newOrUpdatedQuestions)) {
                    $entityManager->remove($question);
                }
            }
            
            // Étape 5: Enregistrer les modifications dans la base de données
            $entityManager->flush();
            
            // Retourner le quizz mis à jour
            return $this->json($quizz, JsonResponse::HTTP_OK, [], ['groups' => ['quizz_details']]);
        } catch (\Exception $e) {
            // Si une exception est levée, retourner un message d'erreur avec le code HTTP 500
            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        }
        
    #[Route('/api/quizz/{id}', name: 'delete_quizz', methods: ['DELETE'])]
    public function delete(Quizz $quizz): JsonResponse
    {

        $this->entityManager->remove($quizz);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/quizz/special/{id}', name: 'get_quizz_details', methods: ['GET'])]
    public function getQuizzDetails(QuizzRepository $quizzRepository,string $id): JsonResponse
    {
        $quizz = $quizzRepository->find($id);

        if (!$quizz) {
            return $this->json(['message' => 'Quizz not found'], Response::HTTP_NOT_FOUND);
        }
        // Assurez-vous de configurer la sérialisation de vos entités pour inclure les questions et les réponses
        $responseData = [
            'id' => $quizz->getId(),
            'title' => $quizz->getTitle(),
            'questions' => [],
        ];
        
        foreach ($quizz->getQuestions() as $question) {
            $responseData['questions'][] = [
                'id' => $question->getId(),
                'text' => $question->getTitle(),
                'options' => [
                    'a' => $question->getOptionA(),
                    'b' => $question->getOptionB(),
                    'c' => $question->getOptionC(),
                    'd' => $question->getOptionD(),
                ],
                'correct_option' => $question->getCorrectOption(),
            ];
        }
        
        return $this->json($responseData);
    }

    #[Route('/api/user/{userId}/quizzes', name: 'get_quizzes_by_user', methods: ['GET'])]
    public function getQuizzesByUser(string $userId): JsonResponse
    {
        // Récupérer l'utilisateur par son ID
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        // Vérifier si l'utilisateur existe
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Récupérer les quizz associés à l'utilisateur
        $quizzes = $this->entityManager->getRepository(Quizz::class)->findBy(['user' => $user]);

        // Sérialiser la liste des quizz pour la réponse
        $data = $this->serializer->serialize($quizzes, 'json', ['groups' => 'quizz_list']);

        return JsonResponse::fromJsonString($data);
    }   
    // Dans votre contrôleur API

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, ValidatorInterface $validator,CacheItemPoolInterface  $cache): JsonResponse
    {

        $data = json_decode($request->getContent(), true);
        // Vérifier le jeton CSRF
        self::tcheckCSRF($data['action'],$data['csrf_token'],$cache);

        // Créez une instance User et mettez à jour avec les données reçues
        $user = new User();

        $user->setUsername($data['username'] ?? '');
        $user->setEmail($data['email'] ?? '');
        $user->setPassword($passwordHasher->hashPassword($user, $data['password'] ?? ''));
        $user->setRoles(['ROLE_USER']);

        // Valider l'instance User
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['message' => 'Validation failed', 'errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
    
        try {
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json(['message' => 'Utilisateur créé avec succès'], Response::HTTP_CREATED);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(['message' => 'L’utilisateur existe déjà'], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la création de l’utilisateur', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $entityManager,CacheItemPoolInterface  $cache): JsonResponse
    {
        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier le jeton CSRF
        self::tcheckCSRF($data['action'],$data['csrf_token'],$cache);
        

        // Vérifier l'email et le mot de passe
        $user = $userRepository->findOneByEmail($data['email']);
        if (!$user || !$passwordEncoder->isPasswordValid($user, $data['password'])) {
            return $this->json(['message' => 'Email ou mot de passe incorrect'], Response::HTTP_UNAUTHORIZED);
        }

        // Générer un token unique et définition de la date d'expiration
        $tokenValue = bin2hex(random_bytes(32));
        $expirationDate = new \DateTime('+30 minutes');

        // Création et sauvegarde du token
        $token = new Token();
        $token->setUser($user);
        $token->setToken($tokenValue);
        $token->setExpirationDate($expirationDate);
        
        $entityManager->persist($token);
        $entityManager->flush();

        // Création du cookie pour le token
        $cookie = new Cookie('authToken', $tokenValue, strtotime('+30 minutes'), '/', null, false, true);
        $userCookie = new Cookie('userId', $user->getId(), strtotime('+30 minutes'), '/', null, false, true);

        // Création de la réponse avec le cookie
        $response = new JsonResponse([
            'message' => 'Connexion réussie',
            'user' => ['id' => $user->getId()],
            'token' => $tokenValue, // optionnel, dépend de si vous voulez exposer le token dans la réponse
            'expiration' => $expirationDate->format('c'),
        ]);

        $response->headers->setCookie($cookie);
        $response->headers->setCookie($userCookie);

        return $response;
    }

    
    
    #[Route('/api/quizz/{quizzId}/submit', name: 'submit_quizz_answers', methods: ['POST'])]
    public function submitQuizzAnswers(Request $request, $quizzId, EntityManagerInterface $entityManager, QuizzRepository $quizzRepository, QuestionRepository $questionRepository,CacheItemPoolInterface  $cache): JsonResponse
    {

        $quizz = $quizzRepository->find($quizzId);
        if (!$quizz) {
            return $this->json(['message' => 'Quizz non trouvé'], Response::HTTP_NOT_FOUND);
        }
    
        $data = json_decode($request->getContent(), true);
        // Vérifier le jeton CSRF
        self::tcheckCSRF($data['action'],$data['csrf_token'],$cache);

        if (!isset($data['answers']) || !is_array($data['answers'])) {
            return $this->json(['message' => 'Format des réponses incorrect'], Response::HTTP_BAD_REQUEST);
        }
        $answers = $data['answers']; // Les réponses sont maintenant attendues sous la forme d'un tableau d'objets
    
        foreach ($answers as $answer) {
            // Vérifiez que chaque réponse a bien les clés 'questionId' et 'selectedOption'
            if (!isset($answer['questionId'], $answer['selectedOption'])) {
                continue; // Ignorez cette réponse si elle ne contient pas les données attendues
            }
    
            $questionId = $answer['questionId'];
            $selectedOptionLetter = $answer['selectedOption'];
    
            $question = $questionRepository->find($questionId);
            if (!$question || $question->getQuizz() !== $quizz) {
                // Si la question n'existe pas ou n'appartient pas au quizz spécifié, on ignore cette réponse
                continue;
            }
    
            $response = new \App\Entity\Response();
            $response->setQuizz($quizz);
            $response->setQuestion($question);
            $response->setSelectedOption($selectedOptionLetter);
            $entityManager->persist($response);
        }
    
        $entityManager->flush();
        return $this->json(['message' => 'Réponses enregistrées avec succès']);
    }
    #[Route('/api/user/{userId}/quizz/stats', name: 'get_quizz_stats_by_user', methods: ['GET'])]
    public function getQuizzStatsByUser(EntityManagerInterface $entityManager,string $userId, QuizzRepository $quizzRepository, QuestionRepository $questionRepository): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($userId);
    
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }
    
        $quizzes = $quizzRepository->findBy(['user' => $user]);
        $stats = [];
    
        foreach ($quizzes as $quizz) {
            $quizzStats = [
                'id' => $quizz->getId(),
                'title' => $quizz->getTitle(),
                'questions' => [],
            ];
    
            foreach ($quizz->getQuestions() as $question) {
                $questionStats = [
                    'id' => $question->getId(),
                    'text' => $question->getTitle(),
                    'options' => [
                        'A' => $question->getOptionA(),
                        'B' => $question->getOptionB(),
                        'C' => $question->getOptionC(),
                        'D' => $question->getOptionD(),
                    ],
                    'responses' => [
                        'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0,
                    ],
                    'correctOption' => $question->getCorrectOption(),
                ];
    
                // Récupérer toutes les réponses pour cette question du quizz spécifique
                $responses = $entityManager->getRepository(\App\Entity\Response::class)->findBy([
                    'quizz' => $quizz, 
                    'question' => $question
                ]);
    
                foreach ($responses as $response) {
                    $selectedOption = strtoupper($response->getSelectedOption());

                    // Incrémenter le compteur pour l'option sélectionnée
                    if (isset($questionStats['responses'][$selectedOption])) {
                        $questionStats['responses'][$selectedOption]++;
                    }
                }
    
                $quizzStats['questions'][] = $questionStats;
            }
    
            $stats[] = $quizzStats;
        }
    
        return $this->json($stats);
    }

    #[Route('/api/csrf-token/{action}', name: 'api_csrf_token', methods: ['GET'])]
    public function getCsrfToken(string $action, CacheItemPoolInterface $cache): JsonResponse
    {
        // Générer un nouveau jeton CSRF
        $csrfToken = bin2hex(random_bytes(32));
    
        // Stocker le jeton CSRF dans le cache
        $cacheItem = $cache->getItem('csrf_token_' . $action);
        $cacheItem->set($csrfToken);
        $cacheItem->expiresAfter(3600); // Expire après 1 heure
        $cache->save($cacheItem);
    
        // Retourner le jeton CSRF dans la réponse JSON
        return $this->json(['csrfToken' => $csrfToken]);
    }

    public function tcheckCSRF(string $action, $csrfToken, CacheItemPoolInterface $cache)
    {
        $cacheItem = $cache->getItem('csrf_token_' . $action);
        $storedCsrfToken = $cacheItem->get();

        if (!$storedCsrfToken || $storedCsrfToken !== $csrfToken) {
            return $this->json(['message' => 'Jeton CSRF invalide', 'token' => $csrfToken], Response::HTTP_BAD_REQUEST);
        }

        $cache->deleteItem('csrf_token_' . $action);
    }
}
