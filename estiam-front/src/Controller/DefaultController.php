<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DefaultController extends AbstractController
{
    // Page d'accueil qui nécessite que l'utilisateur soit connecté
    #[Route('/homePage', name: 'home_page')]
    public function homePage(): Response
    {
        return $this->render('PageQuizz\HomePage.html.twig');
    }

    #[Route('/default', name: 'default')]
    public function default(): Response
    {
        return $this->render('default\index.html.twig',['username' => 'hugo','controller_name'=> 'hugo' ]);
    }

    // Page de modification d'un quizz spécifique
    #[Route('/modifQuizz/{idQuizz}', name: 'modif_quizz')]
    public function modifQuizz( string $idQuizz): Response
    {
        return $this->render('PageQuizz\ModifQuizz.html.twig', ['idQuizz' => $idQuizz]);
    }
    
    // Page pour afficher un quizz
    #[Route('/showQuizz/{idQuizz}', name: 'show_quizz')]
    public function showQuizz( string $idQuizz): Response
    {
        return $this->render('PageQuizz\ShowQuizz.html.twig', ['idQuizz' => $idQuizz]);
    }

    #[Route('/stats', name: 'stats')]
    public function stats(): Response
    {
        return $this->render('PageQuizz\Stats.html.twig');
    }
    // Page pour créer un nouveau quizz
    #[Route('/createQuizz', name: 'create_quizz')]
    public function createQuizz(): Response
    {

        return $this->render('PageQuizz\CreateQuizz.html.twig');
    }

    // Page de connexion
    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('Authentification\login.html.twig');
    }

    // Page d'inscription
    #[Route('/register', name: 'register')]
    public function register(): Response
    {
        return $this->render('Authentification\register.html.twig');
    }
}
