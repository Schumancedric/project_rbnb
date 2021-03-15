<?php
// phpcs:disable
namespace App\Controller;

use App\Entity\Annonces;
use App\Entity\Categories;
use App\Form\AnnoncesType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AnnoncesController extends AbstractController
{
    /**
     * @Route("/annonces", name="annonces")
     */
    public function index(): Response
    {
        return $this->render('annonces/index.html.twig', [
            'controller_name' => 'AnnoncesController',
        ]);
    }

    /**
     * @Route("/annonces/new", name="annonces_annonce")
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
            return $this->redirectToRoute('annonces_show', ['id' => $annonces->getId()]);
        }
        // On affiche le rendu html
        return $this->render('annonces/create.html.twig', [
            'formAnnonces' => $form->createView(),
            // 'editMode' => $annonces->getId() !== null
        ]);
    }

    /**
     * @Route("/annonces/{id}/show", name="annonces_show")
     * @Route ("/annonces/{id}", name="annonces_show", requirements={"id":"\d+"})
     */
    public function show(Annonces $annonces = null, Request $request, ManagerRegistry $manager)
    {
        return $this->render('annonces/show.html.twig', [
            'title' => 'annonces',
            'annonces' => $annonces
        ]);
    }
}
