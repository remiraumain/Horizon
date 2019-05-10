<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Groups("main")
     */
    protected $email;
    /**
     * @Groups("main")
     */
    protected $username;
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("main")
     */
    private $firstName;
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("main")
     */
    private $lastName;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ApiToken", mappedBy="user", orphanRemoval=true)
     */
    private $apiTokens;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="author")
     */
    private $projects;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageFilename;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Project", mappedBy="likeUsers")
     */
    private $likedProjects;

    public function __construct()
    {
        parent::__construct();
        $this->apiTokens = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->projectReferences = new ArrayCollection();
        $this->likedProjects = new ArrayCollection();
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Overridden so that username is now optional
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->setUsername($email);
        return parent::setEmail($email);
    }

    /**
     * @return Collection|ApiToken[]
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): self
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens[] = $apiToken;
            $apiToken->setUser($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): self
    {
        if ($this->apiTokens->contains($apiToken)) {
            $this->apiTokens->removeElement($apiToken);
            // set the owning side to null (unless already changed)
            if ($apiToken->getUser() === $this) {
                $apiToken->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setAuthor($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            // set the owning side to null (unless already changed)
            if ($project->getAuthor() === $this) {
                $project->setAuthor(null);
            }
        }

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function getImagePath()
    {
        return UploaderHelper::PROFILE_IMAGE.'/'.$this->getImageFilename();
    }

    /**
     * @return Collection|Project[]
     */
    public function getLikedProjects(): Collection
    {
        return $this->likedProjects;
    }

    public function addLikedProject(Project $likedProject): self
    {
        if (!$this->likedProjects->contains($likedProject)) {
            $this->likedProjects[] = $likedProject;
            $likedProject->addLikeUser($this);
        }

        return $this;
    }

    public function removeLikedProject(Project $likedProject): self
    {
        if ($this->likedProjects->contains($likedProject)) {
            $this->likedProjects->removeElement($likedProject);
            $likedProject->removeLikeUser($this);
        }

        return $this;
    }
}
