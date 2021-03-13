<?php
// phpcs:disable
namespace App\Controller;

use App\Entity\Users;
use App\Entity\Annonces;
use App\Entity\Categories;
use App\Form\AnnoncesType;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @Route("/home/new", name="home_annonce")
     */
    public function createAnnonces( Request $request, ManagerRegistry $manager )
    {
        $categories = $this->getDoctrine()->getRepository(Categories::class)->findOneBy(['id'=>1]);
        if(!$categories){
          // redirigé vers la page accueil ou affiché une erreur  
        }
    
        $annonces = new Annonces();
        $annonces->setCategories($categories);
        return $this->_processAnnonces($annonces, $request, $manager );
    }

    /**
     * @Route("/home/{id}/edit", name="home_edit")
     */
    public function editAnnonces(Annonces $annonces = null, Request $request, ManagerRegistry $manager)
    {
    }

    /**
     * 
     */
    private function _processAnnonces(Annonces $annonces = null, Request $request, ManagerRegistry $manager)
    {
        
        // On recupere le AnnoncesType (modèle)
        $form = $this->createForm(AnnoncesType::class, $annonces);
        // Analyse de la requête
        $form->handleRequest($request);
        // Validation du formulaire, si il est valide !
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$annonces->getId()) {
                $annonces->setUsers($this->getUser());
            }
            // Persister l'annonce
            $manager->getManager()->persist($annonces);
            // Envoyer l'annonce
            $manager->getManager()->flush();
            // Au moment de la connexion, on redirige vers la page show(affichage de l'annonce)
            return $this->redirectToRoute('home_show', ['id' => $annonces->getId()]);
        }
        // On affiche le rendu html
        return $this->render('home/create.html.twig', [
            'formAnnonces' => $form->createView(),
            // 'editMode' => $annonces->getId() !== null
        ]);
    }
}

