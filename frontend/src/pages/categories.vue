<script setup>
import { ref, onMounted, computed } from "vue";
import { useToast } from "primevue/usetoast";
import Dialog from "primevue/dialog";
import { useApi } from "@/composables/useApi";

const api = useApi();
const toast = useToast();

/* ---------------------------
   STATE
--------------------------- */
const categories = ref([]);

const filterOpen = ref(false);
const filterName = ref("");
const filterSlug = ref("");

const modalVisible = ref(false);
const deleteModalVisible = ref(false);

const editMode = ref(false);
const editSlug = ref(null);

const form = ref({
  name: "",
});

const selectedCategory = ref(null);

/* ---------------------------
   FETCH CATEGORIES
--------------------------- */
const fetchCategories = async () => {
  try {
    const res = await api.get("/categories");
    categories.value = res.data.data;
  } catch {
    toast.add({
      severity: "error",
      summary: "Failed",
      detail: "Could not load categories",
      life: 2500,
    });
  }
};

/* ---------------------------
   FILTER LOGIC
--------------------------- */
const filteredCategories = computed(() => {
  return categories.value.filter((c) => {
    const nameMatch =
      !filterName.value ||
      c.name.toLowerCase().includes(filterName.value.toLowerCase());

    const slugMatch =
      !filterSlug.value ||
      c.slug.toLowerCase().includes(filterSlug.value.toLowerCase());

    return nameMatch && slugMatch;
  });
});

const resetFilters = () => {
  filterName.value = "";
  filterSlug.value = "";
  filterOpen.value = false;
};

/* ---------------------------
   MODALS
--------------------------- */
const openCreateModal = () => {
  editMode.value = false;
  editSlug.value = null;
  form.value.name = "";
  modalVisible.value = true;
};

const openEditModal = (cat) => {
  editMode.value = true;
  editSlug.value = cat.slug;
  form.value.name = cat.name;
  modalVisible.value = true;
};

const closeModal = () => {
  modalVisible.value = false;
  editMode.value = false;
  form.value.name = "";
  editSlug.value = null;
};

/* ---------------------------
   SAVE CATEGORY
--------------------------- */
const saveCategory = async () => {
  if (!form.value.name.trim()) {
    toast.add({
      severity: "warn",
      summary: "Validation",
      detail: "Name is required",
      life: 2500,
    });
    return;
  }

  try {
    if (editMode.value) {
      const res = await api.put(`/categories/${editSlug.value}`, {
        name: form.value.name,
      });

      const index = categories.value.findIndex((c) => c.slug === editSlug.value);
      if (index !== -1) categories.value[index] = res.data.data;

      toast.add({
        severity: "success",
        summary: "Updated",
        detail: "Category updated successfully",
        life: 2500,
      });
    } else {
      const res = await api.post("/categories", { name: form.value.name });
      categories.value.push(res.data.data);

      toast.add({
        severity: "success",
        summary: "Created",
        detail: "Category added successfully",
        life: 2500,
      });
    }

    closeModal();
  } catch {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Request failed",
      life: 3000,
    });
  }
};

/* ---------------------------
   DELETE CATEGORY
--------------------------- */
const confirmDelete = (cat) => {
  selectedCategory.value = cat;
  deleteModalVisible.value = true;
};

const deleteCategory = async () => {
  try {
    await api.delete(`/categories/${selectedCategory.value.slug}`);

    categories.value = categories.value.filter(
      (c) => c.slug !== selectedCategory.value.slug
    );

    toast.add({
      severity: "success",
      summary: "Deleted",
      detail: "Category removed",
      life: 2500,
    });

    selectedCategory.value = null;
    deleteModalVisible.value = false;
  } catch {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Delete failed",
      life: 3000,
    });
  }
};

/* ---------------------------
   INIT
--------------------------- */
onMounted(fetchCategories);
</script>

<template>
  <div class="min-h-screen flex flex-col">

    <!-- HEADER -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Categories</h1>
        <p class="text-gray-500">Manage and organize categories</p>
      </div>

      <div class="flex gap-3">
        <!-- Filter -->
        <button
          @click="filterOpen = !filterOpen"
          class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg shadow flex items-center gap-2"
        >
          <i class="pi pi-filter"></i>
          Filter
        </button>

        <!-- Add -->
        <button
          @click="openCreateModal"
          class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow flex items-center gap-2"
        >
          <i class="pi pi-plus"></i>
          Add
        </button>
      </div>
    </div>

    <!-- FILTER PANEL -->
    <transition name="slide-fade">
      <div
        v-if="filterOpen"
        class="bg-white rounded-xl shadow p-6 border mb-6"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="text-sm">Name</label>
            <input
              v-model="filterName"
              type="text"
              placeholder="Search name..."
              class="mt-1 w-full p-2 border bg-gray-50 rounded-lg"
            />
          </div>

          <div>
            <label class="text-sm">Slug</label>
            <input
              v-model="filterSlug"
              type="text"
              placeholder="Search slug..."
              class="mt-1 w-full p-2 border bg-gray-50 rounded-lg"
            />
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <button @click="resetFilters" class="px-4 py-2 bg-gray-200 rounded-lg">
            Reset
          </button>

          <button
            @click="filterOpen = false"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg"
          >
            Apply
          </button>
        </div>
      </div>
    </transition>

    <!-- TABLE -->
    <div class="bg-white border rounded-xl shadow overflow-hidden">

      <table class="min-w-full">
        <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold">
          <tr>
            <th class="p-4 text-left">ID</th>
            <th class="p-4 text-left">Name</th>
            <th class="p-4 text-left">Slug</th>
            <th class="p-4 text-left">Actions</th>
          </tr>
        </thead>

        <tbody class="divide-y">

          <tr
            v-for="cat in filteredCategories"
            :key="cat.id"
            class="hover:bg-gray-50 transition"
          >
            <td class="p-4 font-mono">#{{ cat.id }}</td>

            <td class="p-4 font-medium text-gray-900">{{ cat.name }}</td>

            <td class="p-4 text-gray-600 text-sm">
              <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs">
                {{ cat.slug }}
              </span>
            </td>

            <td class="p-4 flex gap-2">

              <!-- Edit -->
              <button
                @click="openEditModal(cat)"
                class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200"
              >
                <i class="pi pi-pencil"></i>
              </button>

              <!-- Delete -->
              <button
                @click="confirmDelete(cat)"
                class="px-4 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200"
              >
                <i class="pi pi-trash"></i>
              </button>

            </td>
          </tr>

        </tbody>
      </table>

      <div v-if="filteredCategories.length === 0" class="py-14 text-center text-gray-500">
        <i class="pi pi-inbox text-4xl"></i>
        <p class="mt-3 text-lg">No categories found</p>
      </div>
    </div>

    <!-- ADD / EDIT MODAL -->
    <Dialog
      v-model:visible="modalVisible"
      :modal="true"
      :draggable="false"
      class="w-full md:w-1/3"
      :header="editMode ? 'Edit Category' : 'Add Category'"
      @hide="closeModal"
    >
      <div class="space-y-4">
        <label class="text-sm font-medium">Category Name</label>
        <input
          v-model="form.name"
          type="text"
          class="w-full p-3 border bg-gray-50 rounded-lg mt-1"
          placeholder="Enter category name..."
        />

        <div class="flex justify-end gap-3 mt-4">
          <button class="px-5 py-2 bg-gray-200 rounded-lg" @click="closeModal">
            Cancel
          </button>
          <button
            @click="saveCategory"
            class="px-5 py-2 bg-blue-600 text-white rounded-lg"
          >
            {{ editMode ? "Update" : "Create" }}
          </button>
        </div>
      </div>
    </Dialog>

    <!-- DELETE CONFIRM MODAL -->
    <Dialog
      v-model:visible="deleteModalVisible"
      header="Delete Category"
      :modal="true"
      :draggable="false"
      class="w-full md:w-1/3"
    >
      <p class="text-gray-700">
        Are you sure you want to delete
        <strong class="text-red-600">{{ selectedCategory?.name }}</strong>?
      </p>

      <div class="flex justify-end gap-3 mt-4">
        <button
          class="px-5 py-2 bg-gray-200 rounded-lg"
          @click="deleteModalVisible = false"
        >
          Cancel
        </button>

        <button
          class="px-5 py-2 bg-red-600 text-white rounded-lg"
          @click="deleteCategory"
        >
          Delete
        </button>
      </div>
    </Dialog>

  </div>
</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.35s ease-in-out;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
