<?php

namespace PanKrok\ShoperAppstoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PanKrok\ShoperAppstoreBundle\Repository\BillingsRepository;

/**
 * @ORM\Entity(repositoryClass=BillingsRepository::class)
 */
class Billings
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Shops::class, inversedBy="billings")
     */
    private $shop;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;
    
    public function __toString()
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShop(): ?Shops
    {
        return $this->shop;
    }

    public function setShop(?Shops $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
