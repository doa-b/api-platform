<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter; // !!!IMPORTANT to use the right filter ORM instead of MongodB
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={"get", "post"},
 *     itemOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"cheese_listing:read", "cheese_listing:item:get"}},
 *          },
 *          "put"
 *     },
 *     normalizationContext={"groups"={"cheese_listing:read"}, "swagger_definition_name"="Read"},
 *     denormalizationContext={"groups"={"cheese_listing:write"}, "swagger_definition_name"="Write"},
 *     shortName="cheeses",
 *     attributes={
 *          "pagination_items_per_page" = 10,
 *          "formats"={"jsonld", "json", "html", "jsonhal", "csv"={"text/csv"}}
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CheeseListingRepository")
 * @ApiFilter(BooleanFilter::class, properties={"isPublished"})
 * @ApiFilter(SearchFilter::class, properties={
 *     "title": "partial",
 *     "description": "partial",
 *     "Owner": "exact",
 *     "Owner.username": "partial"
 * })
 * @ApiFilter(RangeFilter::class, properties={"price"})
 * @ApiFilter(PropertyFilter::class, properties={})

 */
class CheeseListing
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"cheese_listing:read", "cheese_listing:write", "user:read"})
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     maxMessage="Describe your cheese in 50 chars or less"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups({"cheese_listing:read"})
     * @Assert\NotBlank()
     */
    private $description;

    /**
     *  The price of this delicous cheese in cents.
     *
     * @ORM\Column(type="integer")
     * @Groups({"cheese_listing:read", "cheese_listing:write", "user:read"})
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="cheeseListings")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"cheese_listing:read", "cheese_listing:write"})
     * @Assert\Valid()
     */
    private $Owner;

    public function __construct(string $title = null) // constructor
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->title = $title;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

//    public function setTitle(string $title): self
//    {
//        $this->title = $title;
//
//        return $this;
//    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * get the first 39 characters of a long description
     *
     * @Groups("cheese_listing:read")
     */
    public function getShortDescription(): ?String
    {
        if (strlen($this->description) <40) {
            return $this->description;
        } else return strip_tags(substr($this->description, 0, 39).'...');

    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * The description of the cheese as raw text.
     *
     * @Groups({"cheese_listing:write"})
     * @SerializedName("description")
     */
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * How long ago in text that this cheese listing was added.
     *
     * @Groups({"cheese_listing:read"})
     */
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans(); // shows it as a string: 5 hours ago
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->Owner;
    }

    public function setOwner(?User $Owner): self
    {
        $this->Owner = $Owner;

        return $this;
    }
}
