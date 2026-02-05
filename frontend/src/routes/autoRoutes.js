export function generateDynamicRoutes(menuLinks) {
    const routes = [];

    menuLinks.forEach(link => {

        // Parent route
        if (!link.children) {
            const cleanPath = link.path.replace(/^\//, "");
            routes.push({
                path: cleanPath,
                name: link.name,
                component: () => import(`@/pages/${cleanPath}.vue`)
            });
        }

        // Child routes
        if (link.children) {
            link.children.forEach(child => {
                const cleanPath = child.path.replace(/^\//, "");
                routes.push({
                    path: cleanPath,
                    name: child.name,
                    component: () => import(`@/pages/${cleanPath}.vue`)
                });
            });
        }

    });

    return routes;
}
