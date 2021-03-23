<?php
// phpcs:disable
namespace App\Controller;

use App\Entity\Annonces;
use App\Entity\Categories;
use App\Form\AnnoncesType;
use App\Repository\AnnoncesRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/annonces")
 */
class AnnoncesController extends AbstractController
{
    /**
     * @Route("/", name="annonces_index", methods={"GET"})
     */
    public function index(AnnoncesRepository $annoncesRepository): Response
    {
        return $this->render('annonces/index.html.twig', [
            'annonces' => $annoncesRepository->findAll(),
        ]);
    }




    /**
     * @Route("/new", name="annonces_new", methods={"GET","POST"})
     */
    public function new(Request $request, ManagerRegistry $manager): Response
    {
        $categories = $this->getDoctrine()->getRepository(Categories::class)->findOneBy(['id'=>1]);
        if (!$categories) {
            // redirigé vers la page accueil ou affiché une erreur
        }

        $annonces = new Annonces();
        $annonces->setCategories($categories);
        return $this->createAnnonces($annonces, $request, $manager);
    }

    private function createAnnonces(Annonces $annonces = null, Request $request, ManagerRegistry $manager)
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
        return $this->render('annonces/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    


    /**
     * @Route("/{id}", name="annonces_show", methods={"GET"})
     */
    public function show(Annonces $annonce): Response
    {
        return $this->render('annonces/show.html.twig', [
            'annonce' => $annonce,
        ]);
    }




    /**
    * @Route("/{id}", name="annonces_delete", methods={"DELETE"})
    */
    public function delete(Request $request, Annonces $annonce): Response
    {
        if ($this->isCsrfTokenValid('delete'.$annonce->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($annonce);
            $entityManager->flush();
        }

        return $this->redirectToRoute('annonces_index');
    }



    
    /**
     * @Route("/{id}/edit", name="annonces_edit", methods={"GET","POST"})
     */
    public function edit(Annonces $annonces, Request $request, ManagerRegistry $manager): Response
    {
        $form = $this->createForm(AnnoncesType::class, $annonces);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $annonces = $form->getData();
            $manager->getManager()->persist($annonces);
            $manager->getManager()->flush();
            $this->addFlash('success', 'les annonces ont été mise à jour');
            return $this->redirectToRoute('annonces_show', ['id' => $annonces->getId()]);
        }
        return $this->render('annonces/edit.html.twig', [
            'form' => $form->createView(),
            'annonce' => $annonces
        ]);
    }
}