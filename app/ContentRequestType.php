<?php

namespace App;

enum ContentRequestType: string
{
    case Removal = 'remove';
    case Correction = 'correct';
    case OptOut = 'opt_out';
    case Rights = 'rights';
    case IllegalContent = 'illegal_content';
    case IntimateImage = 'intimate_image';
    case PrivacyComplaint = 'privacy_complaint';
    case ModerationAppeal = 'moderation_appeal';

    public function label(): string
    {
        return match ($this) {
            self::Removal => 'Remove content',
            self::Correction => 'Correct content',
            self::OptOut => 'Exclude a domain',
            self::Rights => 'Exercise a privacy right',
            self::IllegalContent => 'Report illegal content',
            self::IntimateImage => 'Remove an intimate image',
            self::PrivacyComplaint => 'Make a privacy complaint',
            self::ModerationAppeal => 'Appeal a moderation decision',
        };
    }
}
