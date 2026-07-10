<?php

namespace App;

enum ProjectKind: string
{
    case Application = 'application';
    case Package = 'package';
    case Tool = 'tool';
    case Content = 'content';
}
