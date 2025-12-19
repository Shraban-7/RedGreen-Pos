<template>
    <div class="min-h-screen">
        <Toast position="top-right" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Brands</h1>
                    <p class="text-gray-600">Manage your product brands</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- View Toggle -->
                    <div class="flex bg-gray-100 p-1 rounded-lg">
                        <button @click="viewMode = 'list'" class="px-3 py-1.5 rounded-md transition"
                            :class="viewMode === 'list' ? 'bg-white shadow-sm' : 'text-gray-600 hover:text-gray-800'">
                            <i class="pi pi-list"></i>
                        </button>
                        <button @click="viewMode = 'grid'" class="px-3 py-1.5 rounded-md transition"
                            :class="viewMode === 'grid' ? 'bg-white shadow-sm' : 'text-gray-600 hover:text-gray-800'">
                            <i class="pi pi-th-large"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">
                    {{ editMode ? 'Edit Brand' : 'Create Brand' }}
                </h2>
                <div class="w-3 h-3 rounded-full" :class="editMode ? 'bg-yellow-500' : 'bg-green-500'"></div>
            </div>

            <form @submit.prevent="editMode ? updateBrand() : addBrand()" class="space-y-4">
                <div>
                    <input type="text" v-model="brandName" placeholder="Enter brand name"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
                        required />
                </div>

                <!-- Status Field -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <div class="flex gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" v-model="brandStatus" value="1"
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
                            <span class="ml-2 text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" v-model="brandStatus" value="0"
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
                            <span class="ml-2 text-gray-700">Inactive</span>
                        </label>
                    </div>
                </div>

                <!-- Image Preview Section -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">
                        {{ editMode ? 'Brand Image' : 'Upload Image' }}
                    </label>

                    <!-- Current Image (Edit Mode) -->
                    <div v-if="editMode && currentBrand?.image" class="mb-3">
                        <p class="text-sm text-gray-500 mb-2">Current Image:</p>
                        <div class="flex items-center gap-4">
                            <img :src="currentBrand.image" :alt="currentBrand.name"
                                class="w-24 h-24 object-cover rounded-lg border border-gray-300 shadow-sm">
                            <div class="text-sm text-gray-500">
                                <p>Current brand image</p>
                                <p class="text-xs mt-1">Upload a new image to replace</p>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload Area -->
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors cursor-pointer"
                        @click="$refs.fileInput.click()" @dragover.prevent @drop.prevent="handleDrop">
                        <input type="file" id="brandImage" ref="fileInput" @change="handleImageUpload" accept="image/*"
                            class="hidden" />

                        <div v-if="!previewImage">
                            <div
                                class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="pi pi-cloud-upload text-gray-400 text-xl"></i>
                            </div>
                            <p class="text-gray-600 font-medium mb-1">
                                {{ editMode ? 'Change Image' : 'Upload Image' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                Drag & drop or click to browse
                            </p>
                            <p class="text-xs text-gray-400 mt-2">
                                Supports: JPG, PNG, WebP • Max: 2MB
                            </p>
                        </div>

                        <!-- Preview when image is selected -->
                        <div v-else class="flex flex-col items-center">
                            <div class="relative mb-3">
                                <img :src="previewImage" alt="Preview"
                                    class="w-32 h-32 object-cover rounded-lg border border-gray-300 shadow-sm">
                                <button type="button" @click.stop="removeImage"
                                    class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-md">
                                    <i class="pi pi-times text-xs"></i>
                                </button>
                            </div>
                            <p class="text-sm text-gray-600">Image preview</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Click to choose a different image
                            </p>
                        </div>
                    </div>

                    <!-- Image Info -->
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">
                            {{ editMode ? 'Upload new image (optional)' : 'Image is optional' }}
                        </span>
                        <span v-if="imageFile" class="text-blue-600 font-medium">
                            {{ imageFile.name }} ({{ formatFileSize(imageFile.size) }})
                        </span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition flex items-center justify-center gap-2">
                        <i class="pi" :class="editMode ? 'pi-check' : 'pi-plus'"></i>
                        {{ editMode ? 'Update Brand' : 'Add Brand' }}
                    </button>

                    <button v-if="editMode" type="button" @click="cancelEdit"
                        class="px-6 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-lg transition flex items-center justify-center gap-2">
                        <i class="pi pi-times"></i>
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Stats & Search -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Brands</p>
                        <p class="text-2xl font-bold text-gray-800">{{ brands.length }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="pi pi-building text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:col-span-2">
                <div class="relative">
                    <i class="pi pi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" v-model="searchQuery" placeholder="Search brands by name..."
                        class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" />
                </div>
            </div>
        </div>

        <!-- Brands Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="px-5 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i class="pi" :class="viewMode === 'list' ? 'pi-list' : 'pi-th-large'"></i>
                        All Brands
                        <span class="text-sm font-normal text-gray-500">
                            ({{ filteredBrands.length }} items)
                        </span>
                    </h3>

                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-500">
                            View: {{ viewMode === 'list' ? 'List' : 'Grid' }}
                        </span>
                        <button @click="fetchBrands"
                            class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition"
                            title="Refresh">
                            <i class="pi pi-refresh"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-if="viewMode === 'list' && filteredBrands.length > 0">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Brand
                            </th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Image
                            </th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Created
                            </th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="brand in filteredBrands" :key="brand.id" class="hover:bg-gray-50 transition-colors"
                            :class="editSlug === brand.slug ? 'bg-blue-50' : ''">
                            <td class="py-4 px-6">
                                <span class="font-mono text-sm text-gray-600">#{{ brand.id }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div v-if="brand.image" class="w-10 h-10">
                                        <img :src="brand.image" :alt="brand.name"
                                            class="w-full h-full object-cover rounded-lg border border-gray-200">
                                    </div>
                                    <div v-else
                                        class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="pi pi-building text-gray-600"></i>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-900">{{ brand.name }}</span>
                                        <p class="text-xs text-gray-500 mt-1 font-mono">{{ brand.slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div v-if="brand.image" class="w-12 h-12">
                                    <img :src="brand.image" :alt="brand.name"
                                        class="w-full h-full object-cover rounded-lg border border-gray-200">
                                </div>
                                <div v-else class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i class="pi pi-image text-gray-400"></i>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm px-3 py-1 rounded-full"
                                    :class="brand.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ brand.status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm text-gray-600">{{ brand.created_at }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex gap-2">
                                    <button @click="editBrand(brand)"
                                        class="px-4 py-2 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 rounded-lg transition flex items-center gap-2"
                                        title="Edit">
                                        <i class="pi pi-pencil"></i>
                                        <span class="hidden sm:inline">Edit</span>
                                    </button>
                                    <button @click="toggleStatus(brand)" class="px-4 py-2" :class="brand.status
                                        ? 'bg-red-50 hover:bg-red-100 text-red-700'
                                        : 'bg-green-50 hover:bg-green-100 text-green-700'" title="Toggle Status">
                                        <i class="pi" :class="brand.status ? 'pi-ban' : 'pi-check'"></i>
                                        <span class="hidden sm:inline">
                                            {{ brand.status ? 'Deactivate' : 'Activate' }}
                                        </span>
                                    </button>
                                    <button @click="deleteBrand(brand.slug)"
                                        class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg transition flex items-center gap-2"
                                        title="Delete">
                                        <i class="pi pi-trash"></i>
                                        <span class="hidden sm:inline">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Grid View -->
            <div v-else-if="viewMode === 'grid' && filteredBrands.length > 0" class="p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <div v-for="brand in filteredBrands" :key="brand.id"
                        class="border border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition-all duration-200 group"
                        :class="editSlug === brand.slug ? 'bg-blue-50 border-blue-300' : ''">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div v-if="brand.image" class="w-12 h-12">
                                    <img :src="brand.image" :alt="brand.name"
                                        class="w-full h-full object-cover rounded-xl border border-gray-200 group-hover:scale-105 transition-transform">
                                </div>
                                <div v-else
                                    class="w-12 h-12 bg-linear-to-br from-blue-50 to-blue-100 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i class="pi pi-building text-blue-600 text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 truncate">{{ brand.name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1 font-mono">{{ brand.slug }}</p>
                                </div>
                            </div>
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="flex gap-1">
                                    <button @click="editBrand(brand)"
                                        class="w-8 h-8 flex items-center justify-center bg-white hover:bg-yellow-50 text-yellow-600 rounded-lg transition shadow-sm"
                                        title="Edit">
                                        <i class="pi pi-pencil text-sm"></i>
                                    </button>
                                    <button @click="toggleStatus(brand)"
                                        class="w-8 h-8 flex items-center justify-center bg-white rounded-lg transition shadow-sm"
                                        :class="brand.status
                                            ? 'hover:bg-red-50 text-red-600'
                                            : 'hover:bg-green-50 text-green-600'" title="Toggle Status">
                                        <i class="pi text-sm" :class="brand.status ? 'pi-ban' : 'pi-check'"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                            <span class="text-xs px-2 py-1 rounded"
                                :class="brand.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                {{ brand.status ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-xs text-gray-500">{{ brand.created_at }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="pi pi-building text-gray-400 text-3xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-700 mb-2">No brands found</h4>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    {{
                        searchQuery
                            ? 'No brands match your search. Try different keywords.'
                            : 'Start by adding your first brand using the form above.'
                    }}
                </p>
                <button v-if="searchQuery" @click="searchQuery = ''"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition inline-flex items-center gap-2">
                    <i class="pi pi-times"></i>
                    Clear Search
                </button>
            </div>

            <!-- Footer -->
            <div v-if="filteredBrands.length > 0" class="px-5 py-3 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ filteredBrands.length }}</span>
                        of <span class="font-medium">{{ brands.length }}</span> brands
                        <span v-if="searchQuery" class="ml-2 text-gray-500">
                            • Filtered by "{{ searchQuery }}"
                        </span>
                    </div>
                    <div class="text-sm text-gray-500">
                        View: <span class="font-medium">{{ viewMode === 'list' ? 'List' : 'Grid' }}</span>
                        • Active: <span class="font-medium text-green-600">{{ activeBrandsCount }}</span>
                        • Inactive: <span class="font-medium text-red-600">{{ inactiveBrandsCount }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue"
import axios from "axios"
import { useToast } from 'primevue/usetoast'

const toast = useToast()
const API_URL = "http://pos.test/api/brands"

const brands = ref([])
const brandName = ref("")
const brandStatus = ref("1") 
const editMode = ref(false)
const editSlug = ref(null)
const currentBrand = ref(null)
const searchQuery = ref("")
const viewMode = ref("grid")
const imageFile = ref(null)
const previewImage = ref(null)
const fileInput = ref(null)

const filteredBrands = computed(() => {
    if (!searchQuery.value) return brands.value
    const query = searchQuery.value.toLowerCase()
    return brands.value.filter(brand =>
        brand.name.toLowerCase().includes(query) ||
        brand.slug.toLowerCase().includes(query)
    )
})

const activeBrandsCount = computed(() => {
    return brands.value.filter(brand => brand.status).length
})

const inactiveBrandsCount = computed(() => {
    return brands.value.filter(brand => !brand.status).length
})

const fetchBrands = async () => {
    try {
        const res = await axios.get(API_URL)
        brands.value = res.data.data
    } catch (err) {
        showError('Failed to load brands')
    }
}

const handleImageUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        // Validate file
        if (file.size > 2 * 1024 * 1024) {
            showError('Image size should be less than 2MB')
            return
        }

        if (!file.type.startsWith('image/')) {
            showError('Please upload an image file (JPG, PNG, WebP)')
            return
        }

        imageFile.value = file
        previewImage.value = URL.createObjectURL(file)
    }
}

const handleDrop = (event) => {
    const file = event.dataTransfer.files[0]
    if (file) {
        const fakeEvent = { target: { files: [file] } }
        handleImageUpload(fakeEvent)
    }
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const removeImage = () => {
    imageFile.value = null
    previewImage.value = null
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const addBrand = async () => {
    try {
        const formData = new FormData()
        formData.append('name', brandName.value)
        formData.append('status', brandStatus.value === "1") // Convert to boolean

        if (imageFile.value) {
            formData.append('image', imageFile.value)
        }

        const res = await axios.post(API_URL, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        brands.value.push(res.data.data)
        resetForm()
        showSuccess('Brand added successfully')
    } catch (err) {
        showError('Failed to add brand')
    }
}

const editBrand = (brand) => {
    brandName.value = brand.name
    brandStatus.value = brand.status ? "1" : "0" // Convert boolean to string
    editMode.value = true
    editSlug.value = brand.slug
    currentBrand.value = brand
    removeImage()
}

const updateBrand = async () => {
    try {
        const formData = new FormData()
        formData.append('name', brandName.value)
        formData.append('status', brandStatus.value === "1" ? 1 : 0)
        formData.append('_method', 'PUT')

        if (imageFile.value) {
            formData.append('image', imageFile.value)
        }

        const res = await axios.post(`${API_URL}/${editSlug.value}`, formData)

        const updatedBrand = res.data.data

        const index = brands.value.findIndex(b => b.slug === editSlug.value)
        if (index !== -1) {
            brands.value[index] = updatedBrand
        }

        editSlug.value = updatedBrand.slug

        cancelEdit()
        showSuccess('Brand updated successfully')

    } catch (err) {
        showError('Failed to update brand')
    }
}


const cancelEdit = () => {
    resetForm()
    editMode.value = false
    editSlug.value = null
    currentBrand.value = null
}

const resetForm = () => {
    brandName.value = ""
    brandStatus.value = "1" // Reset to active
    removeImage()
}

const toggleStatus = async (brand) => {
    const newStatus = !brand.status
    const action = newStatus ? 'activate' : 'deactivate'

    if (!confirm(`Are you sure you want to ${action} "${brand.name}"?`)) return

    try {
        const res = await axios.put(`${API_URL}/${brand.slug}/status`, { status: newStatus })
        const index = brands.value.findIndex(b => b.slug === brand.slug)
        if (index !== -1) brands.value[index].status = newStatus
        showSuccess(`Brand ${action}d successfully`)
    } catch (err) {
        showError(`Failed to ${action} brand`)
    }
}

const deleteBrand = async (slug) => {
    if (!confirm('Are you sure you want to delete this brand?')) return

    try {
        await axios.delete(`${API_URL}/${slug}`)
        brands.value = brands.value.filter(brand => brand.slug !== slug)
        showSuccess('Brand deleted successfully')
    } catch (err) {
        showError('Failed to delete brand')
    }
}

const showSuccess = (message) => {
    toast.add({ severity: 'success', summary: 'Success', detail: message, life: 3000 })
}

const showError = (message) => {
    toast.add({ severity: 'error', summary: 'Error', detail: message, life: 4000 })
}

onMounted(fetchBrands)
</script>

<style scoped>
table {
    min-width: 100%;
}

tr {
    transition: background-color 0.15s ease;
}

.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}

@media (max-width: 640px) {
    .grid-cols-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

button:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

input:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 0;
}

img {
    object-fit: cover;
}

/* Drag and drop styles */
.border-dashed:hover {
    border-color: #3b82f6;
    background-color: #f8fafc;
}
</style>