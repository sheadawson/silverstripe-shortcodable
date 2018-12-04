<?php

namespace Silverstripe\Shortcodable\Extensions;

use SilverStripe\Core\Extension;
use Silverstripe\Shortcodable;

class ShortcodableHtmlEditorField extends Extension
{
    public function onBeforeRender()
    {
        $this->owner->setAttribute(
            'data-placeholderclasses',
            implode(',', Shortcodable::get_shortcodable_classes_with_placeholders())
        );
    }
}
