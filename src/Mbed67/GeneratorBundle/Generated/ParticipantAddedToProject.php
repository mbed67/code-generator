<?php

declare (strict_types = 1);

namespace Mbed67\GeneratorBundle\Generated\Project;

use Assert\Assertion;

final class ParticipantAddedToProject
{
    /**
     * @var string
     */
    private $aggregateId;

    /**
     * @var string
     */
    private $participantId;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $infix;

    /**
     * @var string
     */
    private $lastName;

    public function __construct(
        string $aggregateId,
        string $participantId,
        string $firstName,
        string $infix,
        string $lastName
    ) {
        Assertion::uuid($aggregateId, 'Aggregate ID must be a valid UUID.');
        Assertion::uuid($participantId, 'Participantid must be a valid UUID.');
        Assertion::email($firstName, 'Firstname must be a valid email address');
        Assertion::nullOrString($infix, 'Infix must be null or a string.');
        Assertion::nullOrNotBlank($infix, 'Infix must be null or not blank.');

        $this->aggregateId = $aggregateId;
        $this->participantId = $participantId;
        $this->firstName = $firstName;
        $this->infix = $infix;
        $this->lastName = $lastName;
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }
    
    public function participantId(): string
    {
        return $this->participantId;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function infix(): ?string
    {
        return $this->infix;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function toArray(): array
    {
        return [
            'aggregateId' => $this->aggregateId,
            'participantId' => $this->participantId,
            'firstName' => $this->firstName,
            'infix' => $this->infix,
            'lastName' => $this->lastName,
        ];
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            $payload['aggregateId'],
            $payload['participantId'],
            $payload['firstName'],
            $payload['infix'],
            $payload['lastName']
        );
    }
}
