<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @Gedmo\Slug(fields={"name"})
     *
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectReference", mappedBy="project")
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $projectReferences;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectImage", mappedBy="project", orphanRemoval=true)
     */
    private $projectImages;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="likedProjects")
     */
    private $likeUsers;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $likes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="project")
     */
    private $comments;

    public function __construct()
    {
        $this->projectReferences = new ArrayCollection();
        $this->projectImages = new ArrayCollection();
        $this->likeUsers = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->publishedAt !== null;
    }

    /**
     * @return Collection|ProjectReference[]
     */
    public function getProjectReferences(): Collection
    {
        return $this->projectReferences;
    }

    /*public function addProjectReference(ProjectReference $projectReference): self
    {
        if (!$this->projectReferences->contains($projectReference)) {
            $this->projectReferences[] = $projectReference;
            $projectReference->setProject($this);
        }

        return $this;
    }

    public function removeProjectReference(ProjectReference $projectReference): self
    {
        if ($this->projectReferences->contains($projectReference)) {
            $this->projectReferences->removeElement($projectReference);
            // set the owning side to null (unless already changed)
            if ($projectReference->getProject() === $this) {
                $projectReference->setProject(null);
            }
        }

        return $this;
    }*/

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|ProjectImage[]
     */
    public function getProjectImages(): Collection
    {
        return $this->projectImages;
    }

    /*public function addProjectImage(ProjectImage $projectImage): self
    {
        if (!$this->projectImages->contains($projectImage)) {
            $this->projectImages[] = $projectImage;
            $projectImage->setProject($this);
        }

        return $this;
    }

    public function removeProjectImage(ProjectImage $projectImage): self
    {
        if ($this->projectImages->contains($projectImage)) {
            $this->projectImages->removeElement($projectImage);
            // set the owning side to null (unless already changed)
            if ($projectImage->getProject() === $this) {
                $projectImage->setProject(null);
            }
        }

        return $this;
    }*/

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getLikeUsers(): Collection
    {
        return $this->likeUsers;
    }

    public function addLikeUser(User $likeUser): self
    {
        if (!$this->likeUsers->contains($likeUser)) {
            $this->likeUsers[] = $likeUser;
        }
        $this->setLikes();

        return $this;
    }

    public function removeLikeUser(User $likeUser): self
    {
        if ($this->likeUsers->contains($likeUser)) {
            $this->likeUsers->removeElement($likeUser);
        }
        $this->setLikes();

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(): self
    {
        $this->likes = $this->likeUsers->count();

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setProject($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getProject() === $this) {
                $comment->setProject(null);
            }
        }

        return $this;
    }
}
