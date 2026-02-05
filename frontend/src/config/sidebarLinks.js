export const menuLinks = [
  {
    name: "Dashboard",
    path: "/dashboard",
    icon: "pi pi-home",
  },

  {
    name: "Products",
    icon: "pi pi-box",
    children: [
      { name: "Product List", path: "/products" },
      { name: "Add Product", path: "/products/create" }
    ]
  },

  {
    name: "Categories",
    path: "/categories",
    icon: "pi pi-list",
  },

  {
    name: "Brands",
    path: "/brands",
    icon: "pi pi-building",
  },

  {
    name: "Orders",
    path: "/orders",
    icon: "pi pi-shopping-cart",
  },

  {
    name: "Suppliers",
    path: "/suppliers",
    icon: "pi pi-warehouse",
  }
];
