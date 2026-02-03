<script setup>
import { ref, onMounted, computed } from "vue";
import { useToast } from "primevue/usetoast";
import { useApi } from "@/composables/useApi";

const api = useApi();
const toast = useToast();

// Data
const suppliers = ref([]);

// Filters
const filterOpen = ref(false);
const searchName = ref("");
const searchPhone = ref("");
const searchEmail = ref("");

// Modal
const modalVisible = ref(false);
const deleteModalVisible = ref(false);

// Form state
const editMode = ref(false);
const selectedSupplier = ref(null);
const form = ref({
  name: "",
  contact_person: "",
  phone: "",
  email: "",
  address: "",
});

// Fetch all suppliers
const fetchSuppliers = async () => {
  try {
    const res = await api.get("/suppliers");
    suppliers.value = res.data.data;
  } catch {
    toast.add({ severity: "error", summary: "Error", detail: "Failed to load suppliers" });
  }
};

// Filter suppliers
const filteredSuppliers = computed(() => {
  return suppliers.value.filter((sup) => {
    const matchName = !searchName.value || sup.name?.toLowerCase().includes(searchName.value.toLowerCase());
    const matchPhone = !searchPhone.value || sup.phone?.toLowerCase().includes(searchPhone.value.toLowerCase());
    const matchEmail = !searchEmail.value || sup.email?.toLowerCase().includes(searchEmail.value.toLowerCase());
    return matchName && matchPhone && matchEmail;
  });
});

const resetFilters = () => {
  searchName.value = "";
  searchPhone.value = "";
  searchEmail.value = "";
  filterOpen.value = false;
};

// Modal methods
const openCreateModal = () => {
  editMode.value = false;
  selectedSupplier.value = null;
  form.value = {
    name: "",
    contact_person: "",
    phone: "",
    email: "",
    address: "",
  };
  modalVisible.value = true;
};

const openEditModal = (supplier) => {
  editMode.value = true;
  selectedSupplier.value = supplier;
  form.value = { ...supplier };
  modalVisible.value = true;
};

const saveSupplier = async () => {
  try {
    if (editMode.value) {
      const res = await api.put(`/suppliers/${selectedSupplier.value.id}`, form.value);

      const idx = suppliers.value.findIndex((s) => s.id === selectedSupplier.value.id);
      if (idx !== -1) suppliers.value[idx] = res.data.data;

      toast.add({ severity: "success", summary: "Updated", detail: "Supplier updated" });
    } else {
      const res = await api.post(`/suppliers`, form.value);
      suppliers.value.push(res.data.data);

      toast.add({ severity: "success", summary: "Created", detail: "Supplier added" });
    }

    modalVisible.value = false;
  } catch {
    toast.add({ severity: "error", summary: "Error", detail: "Failed to save supplier" });
  }
};

const confirmDelete = (supplier) => {
  selectedSupplier.value = supplier;
  deleteModalVisible.value = true;
};

const deleteSupplier = async () => {
  try {
    await api.delete(`/suppliers/${selectedSupplier.value.id}`);
    suppliers.value = suppliers.value.filter((s) => s.id !== selectedSupplier.value.id);

    toast.add({ severity: "success", summary: "Deleted", detail: "Supplier deleted" });
    deleteModalVisible.value = false;
  } catch {
    toast.add({ severity: "error", summary: "Error", detail: "Failed to delete supplier" });
  }
};

onMounted(fetchSuppliers);
</script>

<template>
  <div class="min-h-screen flex flex-col">

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Suppliers</h1>
        <p class="text-gray-500">Manage your vendor partners</p>
      </div>

      <div class="flex items-center gap-3">

        <!-- Filters toggle -->
        <button
          @click="filterOpen = !filterOpen"
          class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-xl shadow-sm flex items-center gap-2"
        >
          <i class="pi pi-filter"></i>
          Filters
          <i class="pi" :class="filterOpen ? 'pi-chevron-up' : 'pi-chevron-down'"></i>
        </button>

        <!-- Add Supplier -->
        <button
          @click="openCreateModal"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow"
        >
          <i class="pi pi-plus"></i> Add Supplier
        </button>
      </div>
    </div>

    <!-- Slide Filter -->
    <transition name="slide-fade">
      <div v-if="filterOpen" class="bg-white rounded-xl p-5 border shadow-sm mb-6">
        <div class="grid sm:grid-cols-3 gap-4">

          <div>
            <label class="text-sm font-medium text-gray-700">Name</label>
            <input
              v-model="searchName"
              class="mt-1 w-full p-2 rounded-lg border bg-gray-50"
              placeholder="Search name..."
            />
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Phone</label>
            <input
              v-model="searchPhone"
              class="mt-1 w-full p-2 rounded-lg border bg-gray-50"
              placeholder="Search phone..."
            />
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Email</label>
            <input
              v-model="searchEmail"
              class="mt-1 w-full p-2 rounded-lg border bg-gray-50"
              placeholder="Search email..."
            />
          </div>

        </div>

        <div class="flex justify-end gap-3 mt-4">
          <button
            @click="resetFilters"
            class="px-4 py-2 bg-gray-200 rounded-lg"
          >
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

    <!-- Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full table-auto">
        <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-600">
          <tr>
            <th class="p-4 text-left">ID</th>
            <th class="p-4 text-left">Name</th>
            <th class="p-4 text-left">Contact Person</th>
            <th class="p-4 text-left">Phone</th>
            <th class="p-4 text-left">Email</th>
            <th class="p-4 text-left">Address</th>
            <th class="p-4 text-left">Actions</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 text-sm">
          <tr
            v-for="sup in filteredSuppliers"
            :key="sup.id"
            class="hover:bg-gray-50 transition"
          >
            <td class="p-4 font-mono text-gray-700">#{{ sup.id }}</td>
            <td class="p-4 font-medium">{{ sup.name || '-' }}</td>
            <td class="p-4">{{ sup.contact_person || '-' }}</td>
            <td class="p-4">{{ sup.phone || '-' }}</td>
            <td class="p-4">{{ sup.email || '-' }}</td>
            <td class="p-4">{{ sup.address || '-' }}</td>

            <td class="p-4 flex gap-2">
              <button
                @click="openEditModal(sup)"
                class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200"
              >
                <i class="pi pi-pencil"></i>
              </button>

              <button
                @click="confirmDelete(sup)"
                class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
              >
                <i class="pi pi-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div
        v-if="filteredSuppliers.length === 0"
        class="text-center py-16 text-gray-500"
      >
        <i class="pi pi-inbox text-4xl"></i>
        <p class="mt-3 font-medium text-lg">No suppliers found</p>
      </div>
    </div>

    <!-- Delete Modal -->
    <Dialog
      v-model:visible="deleteModalVisible"
      header="Delete Supplier"
      :modal="true"
      :draggable="false"
    >
      <p>Do you want to delete "{{ selectedSupplier?.name }}"?</p>

      <div class="flex justify-end gap-3 mt-4">
        <button class="px-4 py-2 bg-gray-200 rounded-lg" @click="deleteModalVisible = false">
          Cancel
        </button>

        <button class="px-4 py-2 bg-red-600 text-white rounded-lg" @click="deleteSupplier">
          Delete
        </button>
      </div>
    </Dialog>

    <!-- Add/Edit Modal -->
    <Dialog
      v-model:visible="modalVisible"
      :header="editMode ? 'Edit Supplier' : 'Add Supplier'"
      :modal="true"
      :draggable="false"
    >
      <form class="space-y-4" @submit.prevent="saveSupplier">

        <input
          type="text"
          v-model="form.name"
          placeholder="Supplier Name"
          class="w-full p-3 border rounded-lg"
        />

        <input
          type="text"
          v-model="form.contact_person"
          placeholder="Contact Person"
          class="w-full p-3 border rounded-lg"
        />

        <input
          type="text"
          v-model="form.phone"
          placeholder="Phone"
          class="w-full p-3 border rounded-lg"
        />

        <input
          type="email"
          v-model="form.email"
          placeholder="Email"
          class="w-full p-3 border rounded-lg"
        />

        <textarea
          v-model="form.address"
          rows="3"
          placeholder="Address"
          class="w-full p-3 border rounded-lg"
        ></textarea>

        <button class="w-full py-3 bg-blue-600 text-white rounded-lg">
          {{ editMode ? "Update Supplier" : "Create Supplier" }}
        </button>

      </form>
    </Dialog>

  </div>
</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
