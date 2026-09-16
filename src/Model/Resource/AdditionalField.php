<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;

final class AdditionalField extends ResourceModel
{
    /** "type" field */
    public const TYPE_TEXT        = 1;
    public const TYPE_CHECKBOX    = 2;
    public const TYPE_SELECT      = 3;
    public const TYPE_FILE        = 4;
    public const TYPE_HIDDEN      = 5;
    public const TYPE_DESCRIPTION = 6;

    /** "locate" bitmask — where the field is shown; combine with "|" */
    public const LOCATE_USER                     = 1;
    public const LOCATE_USER_ACCOUNT             = 2;
    public const LOCATE_USER_REGISTRATION        = 4;
    public const LOCATE_ORDER_FORM               = 8;
    public const LOCATE_ORDER_ANONYMOUS_REGISTER = 16;
    public const LOCATE_ORDER_ANONYMOUS          = 32;
    public const LOCATE_ORDER_LOGGED             = 64;
    public const LOCATE_CONTACT_FORM             = 128;
    protected string $url = 'additional-fields';
}
