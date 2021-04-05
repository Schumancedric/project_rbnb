<?php
// phpcs:disable
namespace App\Controller;

use App\Entity\Images;
use App\Entity\Annonces;
use App\Entity\Categories;
use App\Form\AnnoncesType;
use App\Repository\AnnoncesRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/annonces")
 */
class AnnoncesController extends AbstractController
{
    /**
     * @isGranted("ROLE_EDITOR")
     * @Route("/", name="annonces_index", methods={"GET"})
     */
    public function index(AnnoncesRepository $annoncesRepository): Response
    {
        return $this->render('annonces/index.html.twig', [
            'annonces' => $annoncesRepository->findAll(),
        ]);
    }




    /**
     * @isGranted("ROLE_EDITOR")
     * @Route("/new", name="annonces_new", methods={"GET","POST"})
     */
    public function new(Request $request, ManagerRegistry $manager): Response
    {
        $categories = $this->getDoctrine()->getRepository(Categories::class)->findOneBy(['id'=>1]);
        if (!$categories) {
            
        }

        $annonces = new Annonces();
        $annonces->setCategories($categories);
        return $this->createAnnonces($annonces, $request, $manager);
    }

    private function createAnnonces(Annonces $annonces = null, Request $request, ManagerRegistry $manager)
    {
        // On récupère le AnnoncesType (modèle)
        $form = $this->createForm(AnnoncesType::class, $annonces);
        // Analyse de la requête
        $form->handleRequest($request);
        // Validation du formulaire, si il est valide !
        if ($form->isSubmitted() && $form->isValid()) {
            
            // On récupère les images
            $images = $form->get('images')->getData();

            // Boucle sur les images
            foreach($images as $image){
                // On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                // On copie le fichier dans le uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );

                // On stocke l'image dans la base  de données (son nom)
                $img = new Images();
                $img->setName($fichier);
                $annonces->addImage($img);
            }
            
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
     * @isGranted("ROLE_EDITOR")
     * @Route("/{id}", name="annonces_show", methods={"GET"})
     */
    public function show(Annonces $annonce): Response
    {
        return $this->render('annonces/show.html.twig', [
            'annonce' => $annonce
        ]);
    }




    /**
    * @isGranted("ROLE_EDITOR")
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
     * @isGranted("ROLE_EDITOR")
     * @Route("/{id}/edit", name="annonces_edit", methods={"GET","POST"})
     */
    public function edit(Annonces $annonces, Request $request, ManagerRegistry $manager): Response
    {
        $form = $this->createForm(AnnoncesType::class, $annonces);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

             // On récupère les images
            $images = $form->get('images')->getData();

            // Boucle sur les images
            foreach($images as $image){
                // On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                // On copie le fichier dans le uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );

                // On stocke l'image dans la base  de données (son nom)
                $img = new Images();
                $img->setName($fichier);
                $annonces->addImage($img);
            }

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


    /**
     * @isGranted("ROLE_EDITOR")
     * @Route("/supprime/image/{id}", name="annonces_delete_images", methods={"DELETE"})
     */
    public function deleteImage(Images $images, Request $request){
        $data = json_decode($request->getContent(), true);
        
        // On vérifie si le token est valide
        if($this->isCsrfTokenValid('delete'.$images->getId(), $data['_token'])){
            $nom = $images->getName();
            unlink($this->getParameter('images_directory').'/'.$nom);

            // On supprime l'entrée de la base
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($images);
            $entityManager->flush();

            // On repond en json
            return new JsonResponse(['success' => 1]);
        }
        else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}