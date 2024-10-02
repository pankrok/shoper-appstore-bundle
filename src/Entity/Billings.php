<?php

namespace PanKrok\ShoperAppstoreBundle\Entity;

use PanKrok\ShoperAppstoreBundle\Repository\BillingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BillingsRepository::class)]
class Billings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'billings')]
    private ?Shops $shop = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShop(): ?Shops
    {
        return $this->shop;
    }

    public function setShop(?Shops $shop): static
    {
        $this->shop = $shop;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
