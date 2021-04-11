<?php
// phpcs:disable
namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder): Response
    {
        // Relier champ de classe Users a l'objet
        $users = new Users();

        // On recupere le registrationType (modèle)
        $form = $this->createForm(RegistrationType::class, $users);

        // Analyse de la requête
        $form->handleRequest($request);

        // Validation du formulaire, si il est valide !
        if($form->isSubmitted() && $form->isValid()){

            // Avant d'enregistrer password, on applique le hash
            $hash = $encoder->encodePassword($users, $users->getPassword());

            // Modification du hash sur password
            $users->setPassword($hash);

            // On inclus un role aux nouveaux utilisateurs connecter
            $users->addRoles("ROLE_EDITOR");

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($users);
            $manager->flush();

            // Au moment de la connexion, on redirige vers la page login
            return $this->redirectToRoute('security_login');
        }

        // On affiche le rendu html
        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    
    /**
     * @Route("/connexion", name="security_login")
     */
    public function login()
    {
        // On affiche le rendu
        return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
        
    }
}
