<?php

namespace App;

enum SocialTextTokenType: string
{
    case Hashtag = 'hashtag';
    case Mention = 'mention';
    case Text = 'text';
}
