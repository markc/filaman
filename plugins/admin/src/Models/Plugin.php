<?php

namespace FilaMan\Admin\Models;

use FilaMan\Admin\Database\Factories\PluginFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return PluginFactory::new();
    }

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'version',
        'enabled',
        'settings',
        'metadata',
        'author',
        'url',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'settings' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the display name, falling back to formatted plugin name
     */
    public function getDisplayNameAttribute($value): string
    {
        if (! empty($value)) {
            return $value;
        }

        // Convert plugin-name to Plugin Name
        return str($this->name)
            ->replace('-plugin', '')
            ->replace('-', ' ')
            ->title()
            ->toString();
    }

    /**
     * Check if plugin is a core plugin
     */
    public function isCorePlugin(): bool
    {
        $corePlugins = ['admin'];

        return in_array($this->name, $corePlugins);
    }

    /**
     * Get plugin configuration
     */
    public function getConfig(?string $key = null, $default = null)
    {
        $settings = $this->settings ?? [];

        if ($key === null) {
            return $settings;
        }

        return data_get($settings, $key, $default);
    }

    /**
     * Set plugin configuration
     */
    public function setConfig(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);

        $this->update(['settings' => $settings]);
    }

    /**
     * Scope for enabled plugins
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope for disabled plugins
     */
    public function scopeDisabled($query)
    {
        return $query->where('enabled', false);
    }
}
