<?php
// phpcs:disable
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        return $this->render('home/home.html.twig');
    }

    /**
     * @Route("/home", name="index")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/home/departement", name="home_departement")
     */
    public function departement(): Response
    {
        return $this->render('home/departement.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/home/departement/ville", name="departement_ville")
     */
    public function ville(): Response
    {
        return $this->render('home/ville.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/home/departement/ville/appartement", name="ville_appartement")
     */
    public function appartement(): Response
    {
        return $this->render('home/appartement.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}

