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
    name: "POS",
    icon: "pi pi-calculator",
    children: [
      { name: "New Sale", path: "/pos/create" },
      { name: "Sales History", path: "/pos/sales" }
    ]
  },

  {
    name: "Suppliers",
    path: "/suppliers",
    icon: "pi pi-warehouse",
  },

  {
    name: "Expenses",
    path: "/expenses",
    icon: "pi pi-money-bill",
  },

  {
    name: "Reports",
    icon: "pi pi-chart-bar",
    children: [
      { name: "Sales Report", path: "/reports/sales" },
      { name: "Customers Report", path: "/reports/customers" },
      { name: "Expenses Report", path: "/reports/expenses" },
      { name: "Overall Report", path: "/reports/overall" }
    ]
  }
];
