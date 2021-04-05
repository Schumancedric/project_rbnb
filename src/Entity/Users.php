<?php
// phpcs:disable
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UsersRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; // contraints d'IDentifiant Unique
use Symfony\Component\Validator\Constraints as Assert; // contraints de validation password et confirm_password

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 * @UniqueEntity(fields={"email"}, message="L'email que vous avez indiqué est dèjà utilisé !")
 */
class Users implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit faire minimum 8 caractères")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Vous n'avez pas taper le même mot de passe")
     */
    public $confirm_password;

    /**
     * @ORM\OneToMany(targetEntity=Annonces::class, mappedBy="users")
     */
    private $annonces;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];

    public function __construct()
    {
        $this->annonces = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
    // identification des methodes
    public function eraseCredentials()
    {
        
    }
    // identification des methodes
    public function getSalt()
    {
        
    }
    // identification des methodes
    public function getRoles()
    {
    // recuperer le roles de user
        $roles = $this->roles;
    // on push le role_user
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    // On inclus un role aux nouveaux utilisateurs connecter
    function addRoles($role) {
        $this->roles[] = $role;
    }

    /**
     * @return Collection|Annonces[]
     */
    public function getAnnonces(): Collection
    {
        return $this->annonces;
    }

    public function addAnnonce(Annonces $annonce): self
    {
        if (!$this->annonces->contains($annonce)) {
            $this->annonces[] = $annonce;
            $annonce->setUsers($this);
        }

        return $this;
    }

    public function removeAnnonce(Annonces $annonce): self
    {
        if ($this->annonces->removeElement($annonce)) {
            // set the owning side to null (unless already changed)
            if ($annonce->getUsers() === $this) {
                $annonce->setUsers(null);
            }
        }

        return $this;
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
