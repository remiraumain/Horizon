<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectReferenceRepository")
 */
class ProjectReference
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("main")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="projectReferences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("main")
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"main", "input"})
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     */
    private $originalFilename;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("main")
     */
    private $mimeType;

    /**
     * @ORM\Column(type="integer")
     */
    private $position = 0;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFilePath(): string
    {
        return UploaderHelper::PROJECT_REFERENCE.'/'.$this->getFilename();
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
