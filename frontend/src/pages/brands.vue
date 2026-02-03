<script setup>
import { ref, computed, onMounted } from "vue";
import Toast from "primevue/toast";
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";
import { useApi } from "@/composables/useApi";

const toast = useToast();
const api = useApi();

/* -----------------------------------------------------
   STATE
----------------------------------------------------- */
const brands = ref([]);

const filterOpen = ref(false);
const filterName = ref("");
const filterSlug = ref("");
const filterStatus = ref("");

const modalVisible = ref(false);
const deleteModalOpen = ref(false);
const statusModalOpen = ref(false);

const editMode = ref(false);
const editSlug = ref(null);
const currentBrand = ref(null);

const form = ref({
    name: "",
    status: "1",
});

// Image upload states
const imageFile = ref(null);
const previewImage = ref(null);
const fileInput = ref(null);

// Modal targets
const deleteTarget = ref(null);
const statusTarget = ref(null);

/* -----------------------------------------------------
   FETCH BRANDS
----------------------------------------------------- */
const fetchBrands = async () => {
    try {
        const res = await api.get("/brands");
        brands.value = res.data.data;
    } catch {
        showError("Failed to load brands");
    }
};

/* -----------------------------------------------------
   FILTERS
----------------------------------------------------- */
const filteredBrands = computed(() => {
    return brands.value.filter((b) => {
        const nameMatch =
            !filterName.value ||
            b.name.toLowerCase().includes(filterName.value.toLowerCase());

        const slugMatch =
            !filterSlug.value ||
            b.slug.toLowerCase().includes(filterSlug.value.toLowerCase());

        const statusMatch =
            filterStatus.value === ""
                ? true
                : String(Number(!!b.status)) === filterStatus.value;

        return nameMatch && slugMatch && statusMatch;
    });
});

const resetFilters = () => {
    filterName.value = "";
    filterSlug.value = "";
    filterStatus.value = "";
    filterOpen.value = false;
};

/* -----------------------------------------------------
   IMAGE HANDLING
----------------------------------------------------- */
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        showError("Image must be less than 2MB");
        return;
    }

    if (!file.type.startsWith("image/")) {
        showError("Invalid image type");
        return;
    }

    imageFile.value = file;
    previewImage.value = URL.createObjectURL(file);
};

const removeImage = () => {
    if (previewImage.value) URL.revokeObjectURL(previewImage.value);
    previewImage.value = null;
    imageFile.value = null;
    if (fileInput.value) fileInput.value.value = "";
};

const formatFileSize = (bytes) => {
    if (!bytes) return "0B";
    const sizes = ["B", "KB", "MB"];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${sizes[i]}`;
};

/* -----------------------------------------------------
   MODAL ACTIONS
----------------------------------------------------- */
const openCreateModal = () => {
    editMode.value = false;
    editSlug.value = null;
    currentBrand.value = null;
    form.value = { name: "", status: "1" };
    removeImage();
    modalVisible.value = true;
};

const openEditModal = (brand) => {
    editMode.value = true;
    editSlug.value = brand.slug;
    currentBrand.value = brand;

    form.value = {
        name: brand.name,
        status: brand.status ? "1" : "0",
    };

    removeImage();
    modalVisible.value = true;
};

const closeBrandModal = () => {
    modalVisible.value = false;
    editMode.value = false;
    editSlug.value = null;
    currentBrand.value = null;
    removeImage();
};

/* -----------------------------------------------------
   SAVE (CREATE / UPDATE)
----------------------------------------------------- */
const saveBrand = async () => {
    if (!form.value.name.trim()) {
        showError("Brand name is required");
        return;
    }

    try {
        const fd = new FormData();

        fd.append("name", form.value.name);
        fd.append("status", form.value.status === "1" ? 1 : 0);

        if (imageFile.value) {
            fd.append("image", imageFile.value);
        }

        let res;

        if (editMode.value) {
            fd.append("_method", "PUT");

            res = await api.post(`/brands/${editSlug.value}`, fd);
            const updated = res.data.data;

            const index = brands.value.findIndex((b) => b.slug === editSlug.value);
            if (index !== -1) brands.value[index] = updated;

            showSuccess("Brand updated successfully");
        } else {
            res = await api.post("/brands", fd);
            brands.value.push(res.data.data);

            showSuccess("Brand created successfully");
        }

        closeBrandModal();
    } catch {
        showError("Failed to save brand");
    }
};

/* -----------------------------------------------------
   DELETE
----------------------------------------------------- */
const confirmDelete = (brand) => {
    deleteTarget.value = brand;
    deleteModalOpen.value = true;
};

const deleteBrand = async () => {
    try {
        await api.delete(`/brands/${deleteTarget.value.slug}`);
        brands.value = brands.value.filter((b) => b.slug !== deleteTarget.value.slug);
        showSuccess("Brand deleted");
        deleteModalOpen.value = false;
    } catch {
        showError("Failed to delete brand");
    }
};

/* -----------------------------------------------------
   STATUS CHANGE
----------------------------------------------------- */
const toggleStatus = (brand) => {
    statusTarget.value = brand;
    statusModalOpen.value = true;
};

const confirmStatusChange = async () => {
    const brand = statusTarget.value;
    const newStatus = brand.status ? 0 : 1;

    try {
        await api.put(`/brands/${brand.slug}/status`, { status: newStatus });

        const index = brands.value.findIndex((b) => b.slug === brand.slug);
        if (index !== -1) brands.value[index].status = newStatus;

        showSuccess(newStatus ? "Brand activated" : "Brand deactivated");
        statusModalOpen.value = false;
    } catch {
        showError("Failed to change status");
    }
};

/* -----------------------------------------------------
   TOAST HELPERS
----------------------------------------------------- */
const showSuccess = (msg) =>
    toast.add({ severity: "success", summary: "Success", detail: msg, life: 2500 });

const showError = (msg) =>
    toast.add({ severity: "error", summary: "Error", detail: msg, life: 3500 });

/* -----------------------------------------------------
   INIT
----------------------------------------------------- */
onMounted(fetchBrands);
</script>



<template>

    <div class="min-h-screen flex flex-col">
        <Toast position="top-right" />

        <!-- HEADER -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Brands</h1>
                <p class="text-gray-500">Manage your product brands</p>
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
                    Add Brand
                </button>
            </div>
        </div>

        <!-- FILTER SLIDE -->
        <transition name="slide-fade">
            <div v-if="filterOpen" class="bg-white rounded-xl shadow p-6 border mb-6">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    <div>
                        <label class="text-sm">Brand Name</label>
                        <input
                            v-model="filterName"
                            type="text"
                            class="mt-1 w-full p-2 border bg-gray-50 rounded-lg"
                            placeholder="Search..."
                        />
                    </div>

                    <div>
                        <label class="text-sm">Slug</label>
                        <input
                            v-model="filterSlug"
                            type="text"
                            class="mt-1 w-full p-2 border bg-gray-50 rounded-lg"
                            placeholder="Search..."
                        />
                    </div>

                    <div>
                        <label class="text-sm">Status</label>
                        <select
                            v-model="filterStatus"
                            class="mt-1 w-full p-2 border bg-gray-50 rounded-lg"
                        >
                            <option value="">All</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
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

        <!-- TABLE -->
        <div class="bg-white border rounded-xl shadow overflow-hidden">

            <table class="w-full">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="p-4 text-left">ID</th>
                        <th class="p-4 text-left">Brand</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="p-4 text-left">Created</th>
                        <th class="p-4 text-left">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    <tr
                        v-for="b in filteredBrands"
                        :key="b.id"
                        class="hover:bg-gray-50"
                    >
                        <td class="p-4 font-mono">#{{ b.id }}</td>

                        <td class="p-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img
                                    v-if="b.image"
                                    :src="b.image"
                                    class="w-full h-full object-cover"
                                />
                                <i v-else class="pi pi-building text-gray-500"></i>
                            </div>

                            <div>
                                <p class="font-medium text-gray-900">{{ b.name }}</p>
                                <p class="text-xs text-gray-500">{{ b.slug }}</p>
                            </div>
                        </td>

                        <td class="p-4">
                            <span
                                class="text-xs px-3 py-1 rounded-full"
                                :class="b.status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                            >
                                {{ b.status ? "Active" : "Inactive" }}
                            </span>
                        </td>

                        <td class="p-4 text-gray-500 text-sm">{{ b.created_at }}</td>

                        <td class="p-4 flex gap-2">

                            <!-- Edit -->
                            <button
                                @click="openEditModal(b)"
                                class="px-3 py-1.5 bg-yellow-100 text-yellow-700 hover:bg-yellow-200 rounded-lg"
                            >
                                <i class="pi pi-pencil"></i>
                            </button>

                            <!-- Status -->
                            <button
                                @click="toggleStatus(b)"
                                class="px-3 py-1.5 rounded-lg"
                                :class="b.status 
                                    ? 'bg-red-100 text-red-700 hover:bg-red-200'
                                    : 'bg-green-100 text-green-700 hover:bg-green-200'"
                            >
                                <i class="pi" :class="b.status ? 'pi-ban' : 'pi-check'"></i>
                            </button>

                            <!-- Delete -->
                            <button
                                @click="confirmDelete(b)"
                                class="px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg"
                            >
                                <i class="pi pi-trash"></i>
                            </button>

                        </td>
                    </tr>

                </tbody>
            </table>

            <div v-if="filteredBrands.length === 0" class="text-center py-16 text-gray-500">
                <i class="pi pi-inbox text-4xl"></i>
                <p class="mt-4 text-lg">No brands found</p>
            </div>
        </div>

        <!-- ADD / EDIT MODAL -->
        <Dialog
            v-model:visible="modalVisible"
            :modal="true"
            class="w-full md:w-1/2 lg:w-2/5"
            :header="editMode ? 'Edit Brand' : 'Add Brand'"
        >
            <div class="space-y-6">

                <!-- Name -->
                <div>
                    <label class="text-sm text-gray-700">Brand Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-1 w-full p-3 border rounded-lg bg-gray-50"
                        placeholder="Enter name..."
                    />
                </div>

                <!-- Status -->
                <div>
                    <label class="text-sm text-gray-700">Status</label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center gap-2">
                            <input type="radio" v-model="form.status" value="1" />
                            Active
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" v-model="form.status" value="0" />
                            Inactive
                        </label>
                    </div>
                </div>

                <!-- Current Image -->
                <div v-if="editMode && currentBrand?.image">
                    <label class="text-sm text-gray-700">Current Image</label>
                    <div class="mt-2 flex items-center gap-4">
                        <img
                            :src="currentBrand.image"
                            class="w-24 h-24 rounded-lg object-cover border"
                        />
                        <p class="text-sm text-gray-500">Upload new image to replace</p>
                    </div>
                </div>

                <!-- Upload -->
                <div>
                    <label class="text-sm text-gray-700">{{ editMode ? "Change Image" : "Upload Image" }}</label>

                    <div
                        class="mt-2 border-2 border-dashed border-gray-300 p-6 rounded-xl text-center cursor-pointer hover:bg-gray-50"
                        @click="fileInput?.click()"
                        @dragover.prevent
                        @drop.prevent="handleDrop"
                    >
                        <input
                            type="file"
                            ref="fileInput"
                            class="hidden"
                            accept="image/*"
                            @change="handleImageUpload"
                        />

                        <div v-if="!previewImage">
                            <i class="pi pi-cloud-upload text-gray-400 text-3xl"></i>
                            <p class="mt-3 text-gray-500 text-sm">Click to upload (max 2MB)</p>
                        </div>

                        <div v-else>
                            <div class="relative mb-2 flex justify-center">
                                <img
                                    :src="previewImage"
                                    class="w-28 h-28 rounded-lg object-cover border shadow"
                                />
                                <button
                                    class="absolute -top-2 -right-2 w-8 h-8 bg-red-600 text-white rounded-full"
                                    @click.stop="removeImage"
                                >
                                    <i class="pi pi-times text-xs"></i>
                                </button>
                            </div>
                            <p class="text-sm text-gray-600">Preview</p>
                        </div>
                    </div>

                    <div v-if="imageFile" class="mt-2 text-right text-sm text-blue-600">
                        {{ imageFile.name }} ({{ formatFileSize(imageFile.size) }})
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 pt-4">
                    <button class="px-5 py-2 bg-gray-200 rounded-lg" @click="modalVisible = false">
                        Cancel
                    </button>

                    <button
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        @click="saveBrand"
                    >
                        {{ editMode ? "Update" : "Create" }}
                    </button>
                </div>

            </div>
        </Dialog>

        <!-- DELETE MODAL -->
        <Dialog
            v-model:visible="deleteModalOpen"
            header="Delete Brand"
            :modal="true"
            class="w-full md:w-1/3"
        >
            <p>
                Are you sure you want to delete
                <strong class="text-red-600">{{ deleteTarget?.name }}</strong>?
            </p>

            <div class="flex justify-end gap-3 mt-4">
                <button class="px-5 py-2 bg-gray-200 rounded-lg" @click="deleteModalOpen = false">
                    Cancel
                </button>
                <button class="px-5 py-2 bg-red-600 text-white rounded-lg" @click="deleteBrand">
                    Delete
                </button>
            </div>
        </Dialog>

        <!-- STATUS MODAL -->
        <Dialog
            v-model:visible="statusModalOpen"
            header="Change Status"
            :modal="true"
            class="w-full md:w-1/3"
        >
            <p>
                Are you sure you want to
                <strong :class="statusTarget?.status ? 'text-red-600' : 'text-green-600'">
                    {{ statusTarget?.status ? 'deactivate' : 'activate' }}
                </strong>
                this brand:
                <strong>{{ statusTarget?.name }}</strong>?
            </p>

            <div class="flex justify-end gap-3 mt-4">
                <button class="px-5 py-2 bg-gray-200 rounded-lg" @click="statusModalOpen = false">Cancel</button>
                <button
                    class="px-5 py-2 text-white rounded-lg"
                    :class="statusTarget?.status ? 'bg-red-600' : 'bg-green-600'"
                    @click="confirmStatusChange"
                >
                    {{ statusTarget?.status ? "Deactivate" : "Activate" }}
                </button>
            </div>
        </Dialog>
    </div>
</template>



<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.35s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
