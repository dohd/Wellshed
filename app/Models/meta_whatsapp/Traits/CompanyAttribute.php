<?php

namespace App\Models\Company\Traits;

trait CompanyAttribute
{
    public function getThemeLogoAttribute()
    {
        if (!$this->attributes['theme_logo']) {
            return 'default_theme.png';
        }
        return $this->attributes['theme_logo'];
    }
    public function getLogoAttribute()
    {
        if (!$this->attributes['logo']) {
            return 'default.png';
        }
        return $this->attributes['logo'];
    }
    public function getIconAttribute()
    {
        if (!$this->attributes['icon']) {
            return 'favicon.ico';
        }
        return $this->attributes['icon'];
    }
}
