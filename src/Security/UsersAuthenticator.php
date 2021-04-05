<?php
// phpcs:disable
namespace App\Security;

use App\Entity\LoginAttempt;
use App\Entity\Users;
use App\Repository\LoginAttemptRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class UsersAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $loginAttemptRepository;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder, LoginAttemptRepository $loginAttemptRepository)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->loginAttemptRepository = $loginAttemptRepository;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        // Début de notre modification: on sauvegarde une tentative de connexion
        $newLoginAttempt = new LoginAttempt($request->getClientIp(), $credentials['email']);

        $this->entityManager->persist($newLoginAttempt);
        $this->entityManager->flush();

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        // Load / create our user however you need.
        // You can do this by calling the user provider, or with custom logic here.
        $username = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $credentials['email']]);

        if (!$username) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $username;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // Deuxième modification, la vérification
        if ($this->loginAttemptRepository->countRecentLoginAttempts($credentials['email']) > 3) {
            // CustomUserMessageAuthenticationException nous permet de définir nous-même le message,
            // qui sera affiché à l'utilisateur (ou bien sa clef de traduction).
            // Attention toutefois à ne pas révéler trop d'informations dans le messages,
            // notamment ne pas indiquer si le compte existe.
            throw new CustomUserMessageAuthenticationException('Vous avez essayé de vous connecter avec un mot'
                .' de passe incorrect de trop nombreuses fois. Veuillez patienter svp avant de ré-essayer.');
        }

        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        // redirect to some "app_homepage" route - of wherever you want
        return new RedirectResponse($this->urlGenerator->generate('app_homepage'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
