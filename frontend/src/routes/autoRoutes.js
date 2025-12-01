export function generateDynamicRoutes(menuLinks) {
    return menuLinks.map(link => {
        const cleanPath = link.path.replace(/^\//, ""); 

        return {
            path: cleanPath,
            name: link.name,
            component: () => import(`@/pages/${cleanPath}.vue`) 
        };
    });
}
