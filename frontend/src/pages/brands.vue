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
const filterStatus = ref(""); // "", "1", "0"

const viewMode = ref("grid");

const modalVisible = ref(false);
const editMode = ref(false);
const editSlug = ref(null);
const currentBrand = ref(null);

const form = ref({
    name: "",
    status: "1",
});

// Image upload
const imageFile = ref(null);
const previewImage = ref(null);
const fileInput = ref(null);

// Delete modal
const deleteModalOpen = ref(false);
const deleteTarget = ref(null);

// Status modal
const statusModalOpen = ref(false);
const statusTarget = ref(null);

/* -----------------------------------------------------
   FETCH BRANDS
----------------------------------------------------- */
const fetchBrands = async () => {
    try {
        const res = await api.get("/brands");
        brands.value = res.data.data;
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to load brands",
            life: 3000
        });
    }
};

/* -----------------------------------------------------
   FILTERS
----------------------------------------------------- */
const filteredBrands = computed(() => {
    return brands.value.filter((b) => {
        const nameMatch = !filterName.value || b.name.toLowerCase().includes(filterName.value.toLowerCase());
        const slugMatch = !filterSlug.value || b.slug.toLowerCase().includes(filterSlug.value.toLowerCase());
        const statusMatch =
            filterStatus.value === "" ? true : String(Number(!!b.status)) === filterStatus.value;

        return nameMatch && slugMatch && statusMatch;
    });
});

const applyFilters = () => {
    filterOpen.value = false;
};

const resetFilters = () => {
    filterName.value = "";
    filterSlug.value = "";
    filterStatus.value = "";
    filterOpen.value = false;
};

/* -----------------------------------------------------
   IMAGE HANDLING
----------------------------------------------------- */
const handleImageUpload = (e) => {
    const file = e.target.files[0];
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
    imageFile.value = null;

    if (previewImage.value) {
        URL.revokeObjectURL(previewImage.value);
        previewImage.value = null;
    }

    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

const handleDrop = (event) => {
    const file = event.dataTransfer.files[0];
    if (file) {
        handleImageUpload({ target: { files: [file] } });
    }
};

const formatFileSize = (bytes) => {
    if (!bytes) return "0 Bytes";
    const units = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${units[i]}`;
};

/* -----------------------------------------------------
   MODAL HELPERS
----------------------------------------------------- */
const openCreateModal = () => {
    form.value = { name: "", status: "1" };
    editMode.value = false;
    editSlug.value = null;
    currentBrand.value = null;
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

        if (imageFile.value) fd.append("image", imageFile.value);

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
    } catch (err) {
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

const deleteSelected = async () => {
    try {
        await api.delete(`/brands/${deleteTarget.value.slug}`);
        brands.value = brands.value.filter((b) => b.slug !== deleteTarget.value.slug);

        showSuccess("Brand deleted successfully");
        deleteModalOpen.value = false;
    } catch (err) {
        showError("Failed to delete brand");
    }
};

/* -----------------------------------------------------
   STATUS MODAL
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

        const index = brands.value.findIndex(b => b.slug === brand.slug);
        if (index !== -1) brands.value[index].status = newStatus;

        showSuccess(`Brand ${newStatus ? "activated" : "deactivated"} successfully`);
        statusModalOpen.value = false;

    } catch (err) {
        showError("Failed to update status");
    }
};

/* -----------------------------------------------------
   TOAST UTILS
----------------------------------------------------- */
const showSuccess = (msg) =>
    toast.add({ severity: "success", summary: "Success", detail: msg, life: 2500 });

const showError = (msg) =>
    toast.add({ severity: "error", summary: "Error", detail: msg, life: 3000 });

/* -----------------------------------------------------
   INIT
----------------------------------------------------- */
onMounted(fetchBrands);
</script>



<template>
    <div class="min-h-screen flex flex-col">
        <Toast position="top-right" />

        <!-- HEADER -->
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Brands</h1>
                <p class="text-gray-500">Manage and organize product brands</p>
            </div>

            <div class="flex gap-3">

                <!-- View Toggle -->
                <div class="flex bg-gray-100 p-1 rounded-xl shadow-sm">
                    <button
                        @click="viewMode = 'list'"
                        class="px-3 py-1.5 rounded-lg transition"
                        :class="viewMode === 'list'
                            ? 'bg-white text-blue-600 shadow-sm'
                            : 'text-gray-600 hover:text-gray-900'"
                    >
                        <i class="pi pi-list"></i>
                    </button>

                    <button
                        @click="viewMode = 'grid'"
                        class="px-3 py-1.5 rounded-lg transition"
                        :class="viewMode === 'grid'
                            ? 'bg-white text-blue-600 shadow-sm'
                            : 'text-gray-600 hover:text-gray-900'"
                    >
                        <i class="pi pi-th-large"></i>
                    </button>
                </div>

                <!-- Filter -->
                <button
                    @click="filterOpen = !filterOpen"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl shadow-sm flex items-center gap-2"
                >
                    <i class="pi pi-filter"></i>
                    Filter
                    <i class="pi" :class="filterOpen ? 'pi-chevron-up' : 'pi-chevron-down'"></i>
                </button>

                <!-- Add -->
                <button
                    @click="openCreateModal"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow-md flex items-center gap-2"
                >
                    <i class="pi pi-plus"></i>
                    Add
                </button>
            </div>
        </div>

        <!-- SLIDE FILTER -->
        <transition name="slide-fade">
            <div v-if="filterOpen" class="bg-white rounded-xl shadow p-6 mb-6">

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">

                    <div>
                        <label class="text-sm font-medium">Brand Name</label>
                        <input
                            v-model="filterName"
                            type="text"
                            class="mt-1 px-4 py-2 rounded-lg border bg-gray-50 w-full"
                            placeholder="Search by name..."
                        />
                    </div>

                    <div>
                        <label class="text-sm font-medium">Slug</label>
                        <input
                            v-model="filterSlug"
                            type="text"
                            class="mt-1 px-4 py-2 rounded-lg border bg-gray-50 w-full"
                            placeholder="Search by slug..."
                        />
                    </div>

                    <div>
                        <label class="text-sm font-medium">Status</label>
                        <select
                            v-model="filterStatus"
                            class="mt-1 px-4 py-2 rounded-lg border bg-gray-50 w-full"
                        >
                            <option value="">All</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="flex justify-end gap-3">
                    <button @click="resetFilters" class="px-4 py-2 bg-gray-200 rounded-xl hover:bg-gray-300">
                        Reset
                    </button>

                    <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Apply
                    </button>
                </div>
            </div>
        </transition>

        <!-- CONTENT -->
        <div class="bg-white rounded-xl shadow">

            <!-- LIST -->
            <div v-if="viewMode === 'list' && filteredBrands.length" class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                        <tr>
                            <th class="py-3 px-6 text-left">ID</th>
                            <th class="py-3 px-6 text-left">Brand</th>
                            <th class="py-3 px-6 text-left">Status</th>
                            <th class="py-3 px-6 text-left">Created</th>
                            <th class="py-3 px-6 text-left">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr
                            v-for="b in filteredBrands"
                            :key="b.id"
                            class="hover:bg-gray-50 transition"
                        >
                            <td class="py-4 px-6 font-mono">#{{ b.id }}</td>

                            <td class="py-4 px-6 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                    <img
                                        v-if="b.image"
                                        :src="b.image"
                                        :alt="b.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <i v-else class="pi pi-building text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ b.name }}</p>
                                    <p class="text-xs text-gray-500">{{ b.slug }}</p>
                                </div>
                            </td>

                            <td class="py-4 px-6">
                                <span
                                    class="text-xs px-3 py-1 rounded-full"
                                    :class="b.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                >
                                    {{ b.status ? "Active" : "Inactive" }}
                                </span>
                            </td>

                            <td class="py-4 px-6 text-gray-500 text-sm">{{ b.created_at }}</td>

                            <td class="py-4 px-6 flex gap-2">
                                <!-- Edit -->
                                <button
                                    @click="openEditModal(b)"
                                    class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200"
                                >
                                    <i class="pi pi-pencil"></i>
                                </button>

                                <!-- Toggle -->
                                <button
                                    @click="toggleStatus(b)"
                                    class="px-4 py-2 rounded-lg"
                                    :class="b.status
                                        ? 'bg-red-100 text-red-700 hover:bg-red-200'
                                        : 'bg-green-100 text-green-700 hover:bg-green-200'"
                                >
                                    <i class="pi" :class="b.status ? 'pi-ban' : 'pi-check'"></i>
                                </button>

                                <!-- Delete -->
                                <button
                                    @click="confirmDelete(b)"
                                    class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
                                >
                                    <i class="pi pi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- GRID -->
            <div
                v-else-if="viewMode === 'grid'"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6"
            >
                <div
                    v-for="b in filteredBrands"
                    :key="b.id"
                    class="p-6 rounded-xl bg-gray-50 shadow hover:shadow-md transition"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-200 flex items-center justify-center">
                                <img
                                    v-if="b.image"
                                    :src="b.image"
                                    :alt="b.name"
                                    class="w-full h-full object-cover"
                                />
                                <i v-else class="pi pi-building text-blue-600 text-lg"></i>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900">{{ b.name }}</h4>
                                <p class="text-xs text-gray-500">{{ b.slug }}</p>
                            </div>
                        </div>

                        <span
                            class="text-xs px-2 py-1 rounded-full"
                            :class="b.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                        >
                            {{ b.status ? "Active" : "Inactive" }}
                        </span>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <button
                            @click="openEditModal(b)"
                            class="flex-1 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200"
                        >
                            <i class="pi pi-pencil"></i>
                        </button>

                        <button
                            @click="toggleStatus(b)"
                            class="flex-1 py-2 rounded-lg"
                            :class="b.status
                                ? 'bg-red-100 text-red-700 hover:bg-red-200'
                                : 'bg-green-100 text-green-700 hover:bg-green-200'"
                        >
                            <i class="pi" :class="b.status ? 'pi-ban' : 'pi-check'"></i>
                        </button>
                    </div>

                    <button
                        @click="confirmDelete(b)"
                        class="w-full mt-2 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
                    >
                        <i class="pi pi-trash"></i> Delete
                    </button>
                </div>
            </div>

            <!-- EMPTY -->
            <div v-else class="text-center py-14 text-gray-500">
                <i class="pi pi-inbox text-4xl"></i>
                <p class="mt-4 text-lg">No brands found</p>
            </div>
        </div>

        <!-- ADD / EDIT MODAL -->
        <Dialog
            v-model:visible="modalVisible"
            :modal="true"
            :draggable="false"
            class="w-full md:w-1/2 lg:w-2/5"
            :header="editMode ? 'Edit Brand' : 'Add Brand'"
        >
            <div class="space-y-5">

                <!-- Name -->
                <div>
                    <label class="text-sm font-medium text-gray-700">Brand Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-1 w-full px-4 py-2 rounded-lg border bg-gray-50"
                        placeholder="Enter brand name..."
                    />
                </div>

                <!-- Status -->
                <div>
                    <label class="text-sm font-medium text-gray-700">Status</label>
                    <div class="flex gap-4 mt-1">
                        <label class="flex items-center gap-2">
                            <input type="radio" v-model="form.status" value="1" />
                            <span>Active</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" v-model="form.status" value="0" />
                            <span>Inactive</span>
                        </label>
                    </div>
                </div>

                <!-- Current Image -->
                <div v-if="editMode && currentBrand?.image">
                    <label class="text-sm font-medium text-gray-700">Current Image</label>
                    <div class="flex items-center gap-4 mt-2">
                        <img :src="currentBrand.image" class="w-24 h-24 rounded-lg object-cover border" />
                        <p class="text-sm text-gray-500">Upload a new image to replace it</p>
                    </div>
                </div>

                <!-- Upload -->
                <div>
                    <label class="text-sm font-medium text-gray-700">
                        {{ editMode ? "Change Image (optional)" : "Upload Image (optional)" }}
                    </label>

                    <div
                        class="mt-2 border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer"
                        @click="fileInput?.click()"
                        @dragover.prevent
                        @drop.prevent="handleDrop"
                    >
                        <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleImageUpload" />

                        <div v-if="!previewImage">
                            <i class="pi pi-cloud-upload text-gray-400 text-3xl"></i>
                            <p class="mt-3 text-gray-500 text-sm">Click to upload | Max 2MB</p>
                        </div>

                        <div v-else>
                            <div class="relative flex justify-center mb-3">
                                <img :src="previewImage" class="w-28 h-28 rounded-lg object-cover border shadow" />
                                <button
                                    type="button"
                                    class="absolute -top-2 -right-2 bg-red-600 text-white w-8 h-8 rounded-full"
                                    @click="removeImage"
                                >
                                    <i class="pi pi-times"></i>
                                </button>
                            </div>
                            <p class="text-sm text-gray-700">Preview</p>
                        </div>
                    </div>

                    <div class="mt-1 text-right text-sm text-blue-600 font-medium" v-if="imageFile">
                        {{ imageFile.name }} ({{ formatFileSize(imageFile.size) }})
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 pt-4">
                    <button class="px-5 py-2 bg-gray-200 rounded-xl" @click="closeBrandModal">
                        Cancel
                    </button>
                    <button class="px-5 py-2 bg-blue-600 text-white rounded-xl" @click="saveBrand">
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
            :draggable="false"
            class="w-full md:w-1/3"
        >
            <p class="text-gray-700">
                Are you sure you want to delete
                <strong class="text-red-600">{{ deleteTarget?.name }}</strong>?
            </p>

            <div class="flex justify-end gap-3 mt-4">
                <button class="px-5 py-2 bg-gray-200 rounded-xl" @click="deleteModalOpen = false">Cancel</button>
                <button class="px-5 py-2 bg-red-600 text-white rounded-xl" @click="deleteSelected">Delete</button>
            </div>
        </Dialog>

        <!-- STATUS MODAL -->
        <Dialog
            v-model:visible="statusModalOpen"
            header="Change Status"
            :modal="true"
            :draggable="false"
            class="w-full md:w-1/3"
        >
            <p class="text-gray-700">
                Are you sure you want to
                <strong :class="statusTarget?.status ? 'text-red-600' : 'text-green-600'">
                    {{ statusTarget?.status ? 'deactivate' : 'activate' }}
                </strong>
                the brand:
                <strong>{{ statusTarget?.name }}</strong>?
            </p>

            <div class="flex justify-end gap-3 mt-4">
                <button class="px-5 py-2 bg-gray-200 rounded-xl" @click="statusModalOpen = false">
                    Cancel
                </button>
                <button
                    class="px-5 py-2 rounded-xl text-white"
                    :class="statusTarget?.status ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                    @click="confirmStatusChange"
                >
                    {{ statusTarget?.status ? 'Deactivate' : 'Activate' }}
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
