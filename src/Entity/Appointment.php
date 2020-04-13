<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Appointment {

    use IdTrait;
    use UuidTrait;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     * @var string|null
     */
    private $externalId = null;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @var string|null
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     * @Assert\NotNull()
     * @var \DateTime|null
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\GreaterThan(propertyPath="start")
     * @var \DateTime|null
     */
    private $end;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $location;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $allDay = true;

    /**
     * @ORM\ManyToMany(targetEntity="StudyGroup", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="appointment_studygroups",
     *     joinColumns={@ORM\JoinColumn(name="grade", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="studygroup", onDelete="CASCADE")}
     * )
     * @var ArrayCollection<StudyGroup>
     */
    private $studyGroups;

    /**
     * @ORM\ManyToMany(targetEntity="Teacher", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="appointment_organizers",
     *     joinColumns={@ORM\JoinColumn(name="appointment", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="teacher", onDelete="CASCADE")}
     * )
     * @var ArrayCollection<Teacher>
     */
    private $organizers;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $externalOrganizers;

    /**
     * @ORM\ManyToOne(targetEntity="AppointmentCategory")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @var AppointmentCategory
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="UserTypeEntity")
     * @ORM\JoinTable(name="appointment_visibilities",
     *     joinColumns={@ORM\JoinColumn(onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE")}
     * )
     * @var ArrayCollection<UserTypeEntity>
     */
    private $visibilities;

    public function __construct() {
        $this->uuid = Uuid::uuid4();

        $this->studyGroups = new ArrayCollection();
        $this->organizers = new ArrayCollection();
        $this->visibilities = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getExternalId(): ?string {
        return $this->externalId;
    }

    /**
     * @param string|null $externalId
     * @return Appointment
     */
    public function setExternalId(?string $externalId): Appointment {
        $this->externalId = $externalId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Appointment
     */
    public function setTitle(?string $title): Appointment {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string {
        return $this->content;
    }

    /**
     * @param string|null $content
     * @return Appointment
     */
    public function setContent(?string $content): Appointment {
        $this->content = $content;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStart(): ?\DateTime {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     * @return Appointment
     */
    public function setStart(?\DateTime $start): Appointment {
        $this->start = $start;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): ?\DateTime {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return Appointment
     */
    public function setEnd(?\DateTime $end): Appointment {
        $this->end = $end;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string {
        return $this->location;
    }

    /**
     * @param string|null $location
     * @return Appointment
     */
    public function setLocation(?string $location): Appointment {
        $this->location = $location;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllDay(): bool {
        return $this->allDay;
    }

    /**
     * @param bool $allDay
     * @return Appointment
     */
    public function setAllDay(bool $allDay): Appointment {
        $this->allDay = $allDay;
        return $this;
    }

    /**
     * @return AppointmentCategory|null
     */
    public function getCategory(): ?AppointmentCategory {
        return $this->category;
    }

    /**
     * @param AppointmentCategory $category
     * @return Appointment
     */
    public function setCategory(AppointmentCategory $category): Appointment {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExternalOrganizers(): ?string {
        return $this->externalOrganizers;
    }

    /**
     * @param string|null $externalOrganizers
     * @return Appointment
     */
    public function setExternalOrganizers(?string $externalOrganizers): Appointment {
        $this->externalOrganizers = $externalOrganizers;
        return $this;
    }

    public function addOrganizer(Teacher $teacher) {
        $this->organizers->add($teacher);
    }

    public function removeOrganizer(Teacher $teacher) {
        $this->organizers->removeElement($teacher);
    }

    /**
     * @return ArrayCollection<Teacher>
     */
    public function getOrganizers() {
        return $this->organizers;
    }

    public function addStudyGroup(StudyGroup $studyGroup) {
        $this->studyGroups->add($studyGroup);
    }

    public function removeStudyGroup(StudyGroup $studyGroup) {
        $this->studyGroups->removeElement($studyGroup);
    }

    /**
     * @return ArrayCollection<StudyGroup>
     */
    public function getStudyGroups() {
        return $this->studyGroups;
    }

    public function addVisibility(UserTypeEntity $visibility) {
        $this->visibilities->add($visibility);
    }

    public function removeVisibility(UserTypeEntity $visibility) {
        $this->visibilities->removeElement($visibility);
    }

    /**
     * @return Collection<UserTypeEntity>
     */
    public function getVisibilities(): Collection {
        return $this->visibilities;
    }
}