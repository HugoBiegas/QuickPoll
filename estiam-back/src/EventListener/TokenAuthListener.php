<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\UserRepository;
use App\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class TokenAuthListener
{
    private $userRepository;
    private $tokenRepository;
    private $entityManager;

    public function __construct(UserRepository $userRepository, TokenRepository $tokenRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
        $this->entityManager = $entityManager;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Liste des chemins à exclure de la vérification
        $excludedPaths = [
            '/api/login',
            '/api/register',
            '/api/quizz/special',
        ];

        
        // Vérifier si le chemin actuel commence par un des chemins exclus
        $isExcluded = false;
        foreach ($excludedPaths as $excludedPath) {
            if (strpos($path, $excludedPath) === 0) {
                $isExcluded = true;
                break;
            }
        }

        // Pour /api/quizz/{quizzId}/submit, l'exclusion est un peu plus spécifique
        if (preg_match('#^/api/quizz/[\w-]+/submit$#', $path)) {
            $isExcluded = true;
        }

        // Si le chemin est exclu, ne pas continuer avec la vérification d'authentification
        if ($isExcluded) {
            return;
        }

        // Exécution de la logique de vérification d'authentification
        $userId = $request->cookies->get('userId');
        $tokenValue = $request->cookies->get('authToken');

        // Vérifier l'existence de l'utilisateur
        $user = $this->userRepository->find($userId);
        if (!$user) {
            $event->setResponse(new Response('Utilisateur non trouvé', Response::HTTP_UNAUTHORIZED));
            return;
        }

        // Purger périodiquement les tokens expirés
        if (rand(1, 3) === 1) {
            $this->tokenRepository->purgeExpiredTokens();
        }

        // Vérifier la validité du token
        $token = $this->tokenRepository->findOneBy(['token' => $tokenValue, 'user' => $user]);
        if (!$token || $token->getExpirationDate() < new \DateTime()) {
            $event->setResponse(new Response('Token invalide ou expiré', Response::HTTP_UNAUTHORIZED));
            return;
        }

        // Tout est bon, l'utilisateur peut continuer
        // Pas besoin d'agir ici si la vérification est passée; la requête continuera vers le contrôleur prévu
    }
}
