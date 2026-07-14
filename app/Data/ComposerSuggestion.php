<?php

namespace App\Data;

use App\ComposerSuggestionType;

final readonly class ComposerSuggestion
{
    /**
     * @param  array{url: string, alt: string}|null  $image
     */
    public function __construct(
        public ComposerSuggestionType $type,
        public int $id,
        public string $label,
        public string $description,
        public string $replacement,
        public ?array $image = null,
        public bool $verified = false,
    ) {}

    /**
     * @return array{
     *     type: string,
     *     id: int,
     *     label: string,
     *     description: string,
     *     replacement: string,
     *     image: array{url: string, alt: string}|null,
     *     verified: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'id' => $this->id,
            'label' => $this->label,
            'description' => $this->description,
            'replacement' => $this->replacement,
            'image' => $this->image,
            'verified' => $this->verified,
        ];
    }
}
