<script setup>
import { ref, computed, onMounted } from "vue";
import { useToast } from "primevue/usetoast";
import Dialog from "primevue/dialog"; // ✅ import locally (recommended)
import { useApi } from "@/composables/useApi";

const toast = useToast();
const api = useApi();

// ─────────────────────────────
// STATE
// ─────────────────────────────
const categories = ref([]);
const searchQuery = ref("");
const searchSlug = ref("");
const filterOpen = ref(false);
const viewMode = ref("grid");

// Add/Edit modal
const modalVisible = ref(false);
const form = ref({ name: "" });
const editMode = ref(false);
const editSlug = ref(null);

// Delete modal
const deleteModalOpen = ref(false);
const selectedCategory = ref(null);

// ─────────────────────────────
// FETCH
// ─────────────────────────────
const fetchCategories = async () => {
  try {
    const res = await api.get("/categories");
    categories.value = res.data.data;
  } catch (err) {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Failed to load categories",
      life: 3000,
    });
  }
};

// ─────────────────────────────
// FILTER
// ─────────────────────────────
const filteredCategories = computed(() => {
  return categories.value.filter((cat) => {
    const nameMatch =
      !searchQuery.value ||
      cat.name.toLowerCase().includes(searchQuery.value.toLowerCase());

    const slugMatch =
      !searchSlug.value ||
      cat.slug.toLowerCase().includes(searchSlug.value.toLowerCase());

    return nameMatch && slugMatch;
  });
});

const applyFilters = () => {
  filterOpen.value = false;
};

const resetFilters = () => {
  searchQuery.value = "";
  searchSlug.value = "";
  filterOpen.value = false;
};

// ─────────────────────────────
// ADD / EDIT MODAL
// ─────────────────────────────
const openCreateModal = () => {
  form.value.name = "";
  editMode.value = false;
  editSlug.value = null;
  modalVisible.value = true;
};

const openEditModal = (cat) => {
  form.value.name = cat.name;
  editMode.value = true;
  editSlug.value = cat.slug;
  modalVisible.value = true;
};

const closeCategoryModal = () => {
  modalVisible.value = false;
  form.value.name = "";
  editMode.value = false;
  editSlug.value = null;
};

// ─────────────────────────────
// SAVE (CREATE / UPDATE)
// ─────────────────────────────
const saveCategory = async () => {
  if (!form.value.name?.trim()) {
    toast.add({
      severity: "warn",
      summary: "Validation",
      detail: "Category name is required",
      life: 2500,
    });
    return;
  }

  try {
    if (editMode.value) {
      const res = await api.put(`/categories/${editSlug.value}`, {
        name: form.value.name,
      });

      const idx = categories.value.findIndex((c) => c.slug === editSlug.value);
      if (idx !== -1) categories.value[idx] = res.data.data;

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

    closeCategoryModal();
  } catch (err) {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Request failed",
      life: 3000,
    });
  }
};

// ─────────────────────────────
// DELETE MODAL
// ─────────────────────────────
const confirmDelete = (cat) => {
  selectedCategory.value = cat;
  deleteModalOpen.value = true;
};

const deleteSelected = async () => {
  if (!selectedCategory.value?.slug) return;

  try {
    await api.delete(`/categories/${selectedCategory.value.slug}`);
    categories.value = categories.value.filter(
      (c) => c.slug !== selectedCategory.value.slug
    );

    toast.add({
      severity: "success",
      summary: "Deleted",
      detail: "Category deleted",
      life: 2500,
    });

    deleteModalOpen.value = false;
    selectedCategory.value = null;
  } catch (err) {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Failed to delete",
      life: 3000,
    });
  }
};

// ─────────────────────────────
// INIT
// ─────────────────────────────
onMounted(fetchCategories);
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- PAGE HEADER -->
    <div class="mb-4 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Categories</h1>
        <p class="text-gray-500">Manage and organize your product categories</p>
      </div>

      <div class="flex gap-3">
        <!-- VIEW TOGGLE -->
        <div class="flex bg-gray-100 p-1 rounded-xl shadow-sm">
          <button
            @click="viewMode = 'list'"
            class="px-3 py-1.5 rounded-lg transition font-medium"
            :class="
              viewMode === 'list'
                ? 'bg-white text-blue-600 shadow-sm'
                : 'text-gray-600 hover:text-gray-900'
            "
          >
            <i class="pi pi-list"></i>
          </button>

          <button
            @click="viewMode = 'grid'"
            class="px-3 py-1.5 rounded-lg transition font-medium"
            :class="
              viewMode === 'grid'
                ? 'bg-white text-blue-600 shadow-sm'
                : 'text-gray-600 hover:text-gray-900'
            "
          >
            <i class="pi pi-th-large"></i>
          </button>
        </div>

        <!-- FILTER BUTTON -->
        <button
          @click="filterOpen = !filterOpen"
          class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl shadow-sm flex items-center gap-2"
        >
          <i class="pi pi-filter"></i>
          Filter
          <i class="pi" :class="filterOpen ? 'pi-chevron-up' : 'pi-chevron-down'"></i>
        </button>

        <!-- ADD CATEGORY -->
        <button
          @click="openCreateModal"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow-md flex items-center gap-2"
        >
          <i class="pi pi-plus"></i>
          Add
        </button>
      </div>
    </div>

    <!-- SLIDE-DOWN FILTER -->
    <transition name="slide-fade">
      <div v-if="filterOpen" class="bg-white rounded-xl shadow-md border border-gray-200 p-5 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
          <div>
            <label class="text-sm font-medium text-gray-700">Category Name</label>
            <input
              v-model="searchQuery"
              type="text"
              class="mt-1 w-full px-4 py-2 rounded-lg border bg-gray-50"
              placeholder="Type category name..."
            />
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Slug</label>
            <input
              v-model="searchSlug"
              type="text"
              class="mt-1 w-full px-4 py-2 rounded-lg border bg-gray-50"
              placeholder="Type slug..."
            />
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <button @click="resetFilters" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">
            Reset
          </button>
          <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow">
            Apply Filters
          </button>
        </div>
      </div>
    </transition>

    <!-- MAIN CONTENT -->
    <div class="bg-white rounded-xl shadow-md">
      <!-- LIST VIEW -->
      <div v-if="viewMode === 'list' && filteredCategories.length" class="overflow-x-auto">
        <table class="min-w-full bg-transparent">
          <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider">
            <tr>
              <th class="py-3 px-6 text-left">ID</th>
              <th class="py-3 px-6 text-left">Category</th>
              <th class="py-3 px-6 text-left">Slug</th>
              <th class="py-3 px-6 text-left">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y">
            <tr v-for="cat in filteredCategories" :key="cat.id" class="hover:bg-gray-50 transition">
              <td class="py-4 px-6 font-mono text-gray-700">#{{ cat.id }}</td>

              <td class="py-4 px-6 flex items-center gap-3 font-medium text-gray-900">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                  <i class="pi pi-folder text-blue-600"></i>
                </div>
                {{ cat.name }}
              </td>

              <td class="py-4 px-6">
                <span class="text-xs bg-gray-100 px-3 py-1 rounded-full text-gray-600">
                  {{ cat.slug }}
                </span>
              </td>

              <td class="py-4 px-6 flex gap-2">
                <button
                  @click="openEditModal(cat)"
                  class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-xl hover:bg-yellow-200"
                >
                  <i class="pi pi-pencil"></i>
                </button>

                <button
                  @click="confirmDelete(cat)"
                  class="px-4 py-2 bg-red-100 text-red-700 rounded-xl hover:bg-red-200"
                >
                  <i class="pi pi-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- GRID VIEW -->
      <div
        v-else-if="viewMode === 'grid'"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6"
      >
        <div
          v-for="cat in filteredCategories"
          :key="cat.id"
          class="p-6 rounded-2xl bg-gray-50 shadow-sm hover:shadow-lg transition"
        >
          <h4 class="font-semibold text-gray-900">{{ cat.name }}</h4>
          <p class="text-xs text-gray-500">{{ cat.slug }}</p>

          <div class="flex gap-2 mt-4">
            <button
              @click="openEditModal(cat)"
              class="flex-1 py-2 bg-yellow-100 text-yellow-700 rounded-xl hover:bg-yellow-200"
            >
              <i class="pi pi-pencil"></i> Edit
            </button>

            <button
              @click="confirmDelete(cat)"
              class="flex-1 py-2 bg-red-100 text-red-700 rounded-xl hover:bg-red-200"
            >
              <i class="pi pi-trash"></i> Delete
            </button>
          </div>
        </div>
      </div>

      <!-- EMPTY -->
      <div v-else class="text-center py-16 text-gray-500">
        <i class="pi pi-inbox text-4xl"></i>
        <p class="mt-4 text-lg font-medium">No categories found.</p>
      </div>
    </div>

    <!-- ✅ ADD / EDIT MODAL -->
    <Dialog
      v-model:visible="modalVisible"
      :modal="true"
      :draggable="false"
      :closable="true"
      class="w-full md:w-1/3"
      :header="editMode ? 'Edit Category' : 'Add Category'"
      @hide="closeCategoryModal"
    >
      <div class="space-y-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Category Name</label>
          <input
            v-model="form.name"
            type="text"
            class="mt-1 w-full px-4 py-2 rounded-lg border bg-gray-50"
            placeholder="Enter category name..."
          />
        </div>

        <div class="flex justify-end gap-3">
          <button
            type="button"
            class="px-5 py-2 bg-gray-200 rounded-xl hover:bg-gray-300"
            @click="closeCategoryModal"
          >
            Cancel
          </button>

          <button
            type="button"
            class="px-5 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700"
            @click="saveCategory"
          >
            <i class="pi" :class="editMode ? 'pi-check' : 'pi-plus'"></i>
            {{ editMode ? "Update" : "Create" }}
          </button>
        </div>
      </div>
    </Dialog>

    <!-- DELETE CONFIRMATION MODAL -->
    <Dialog
      v-model:visible="deleteModalOpen"
      header="Confirm Delete"
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
          @click="deleteModalOpen = false"
          class="px-5 py-2 bg-gray-200 rounded-xl hover:bg-gray-300"
        >
          Cancel
        </button>

        <button
          @click="deleteSelected"
          class="px-5 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700"
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
