<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class PublicRouteResolver
{
    /** @var array<string, string> */
    private const ROUTES = [
        'search' => 'search.php',
        'map' => 'map.php',
        'mechanic' => 'single-mechanic.php',
    ];

    public function register(): void
    {
        add_rewrite_rule('^search/?$', 'index.php?mechanicyab_route=search', 'top');
        add_rewrite_rule('^map/?$', 'index.php?mechanicyab_route=map', 'top');
        add_rewrite_rule('^mechanic/([^/]+)/?$', 'index.php?mechanicyab_route=mechanic&mechanicyab_slug=$matches[1]', 'top');
        add_filter('query_vars', [$this, 'queryVars']);
        add_filter('template_include', [$this, 'template']);
    }

    /** @return array<string, string> */
    public function supportedRoutes(): array
    {
        return self::ROUTES;
    }

    /** @param list<string> $vars @return list<string> */
    public function queryVars(array $vars): array
    {
        $vars[] = 'mechanicyab_route';
        $vars[] = 'mechanicyab_slug';
        return $vars;
    }

    public function route(): string
    {
        $route = (string) get_query_var('mechanicyab_route');
        return array_key_exists($route, self::ROUTES) ? $route : '';
    }

    public function template(string $template): string
    {
        $route = $this->route();
        if ($route === '') {
            return $template;
        }
        $candidate = locate_template(self::ROUTES[$route]);
        return $candidate !== '' ? $candidate : $template;
    }
}
