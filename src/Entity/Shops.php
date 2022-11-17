<?php

namespace PanKrok\ShoperAppstoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;

/**
 * @ORM\Entity(repositoryClass=ShopsRepository::class)
 */
class Shops
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $shop = null;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $shop_url = null;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    private $version = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private $installed = 0;

    /**
     * @ORM\OneToOne(targetEntity=AccessTokens::class, mappedBy="shop", cascade={"persist", "remove"})
     */
    private $accessTokens;

    /**
     * @ORM\OneToMany(targetEntity=Billings::class, mappedBy="shop")
     */
    private $billings;

    /**
     * @ORM\OneToMany(targetEntity=Subscriptions::class, mappedBy="shop")
     */
    private $subscriptions;

    public function __construct()
    {
        $this->billings = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
    }
    
    public function __toString()
    {
        return $this->shop_url;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getShop(): ?string
    {
        return $this->shop;
    }

    public function setShop(string $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getShopUrl(): ?string
    {
        return $this->shop_url;
    }

    public function setShopUrl(string $shop_url): self
    {
        $this->shop_url = $shop_url;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getInstalled(): ?bool
    {
        return $this->installed;
    }

    public function setInstalled(bool $installed): self
    {
        $this->installed = $installed;

        return $this;
    }

    public function getAccessTokens(): ?AccessTokens
    {
        return $this->accessTokens;
    }

    public function setAccessTokens(?AccessTokens $accessTokens): self
    {
        // unset the owning side of the relation if necessary
        if (null === $accessTokens && null !== $this->accessTokens) {
            $this->accessTokens->setShop(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $accessTokens && $accessTokens->getShop() !== $this) {
            $accessTokens->setShop($this);
        }

        $this->accessTokens = $accessTokens;

        return $this;
    }

    /**
     * @return Collection|Billings[]
     */
    public function getBillings(): Collection
    {
        return $this->billings;
    }

    public function addBilling(Billings $billing): self
    {
        if (!$this->billings->contains($billing)) {
            $this->billings[] = $billing;
            $billing->setShop($this);
        }

        return $this;
    }

    public function removeBilling(Billings $billing): self
    {
        if ($this->billings->removeElement($billing)) {
            // set the owning side to null (unless already changed)
            if ($billing->getShop() === $this) {
                $billing->setShop(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Subscriptions[]
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscriptions $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setShop($this);
        }

        return $this;
    }

    public function removeSubscription(Subscriptions $subscription): self
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getShop() === $this) {
                $subscription->setShop(null);
            }
        }

        return $this;
    }
}
