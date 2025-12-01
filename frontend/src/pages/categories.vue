<template>
    <div class="min-h-screen">
        <Toast position="top-right" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Categories</h1>
                    <p class="text-gray-600">Manage your product categories</p>
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
                    {{ editMode ? 'Edit Category' : 'Create Category' }}
                </h2>
                <div class="w-3 h-3 rounded-full" :class="editMode ? 'bg-yellow-500' : 'bg-green-500'"></div>
            </div>

            <form @submit.prevent="editMode ? updateCategory() : addCategory()" class="space-y-4">
                <div>
                    <input type="text" v-model="categoryName" placeholder="Enter category name"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
                        required />
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition flex items-center justify-center gap-2">
                        <i class="pi" :class="editMode ? 'pi-check' : 'pi-plus'"></i>
                        {{ editMode ? 'Update' : 'Add Category' }}
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
                        <p class="text-sm text-gray-500">Total Categories</p>
                        <p class="text-2xl font-bold text-gray-800">{{ categories.length }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="pi pi-tags text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:col-span-2">
                <div class="relative">
                    <i class="pi pi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" v-model="searchQuery" placeholder="Search categories by name or slug..."
                        class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" />
                </div>
            </div>
        </div>

        <!-- Categories Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="px-5 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i class="pi" :class="viewMode === 'list' ? 'pi-list' : 'pi-th-large'"></i>
                        All Categories
                        <span class="text-sm font-normal text-gray-500">
                            ({{ filteredCategories.length }} items)
                        </span>
                    </h3>

                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-500">
                            View: {{ viewMode === 'list' ? 'List' : 'Grid' }}
                        </span>
                        <button @click="fetchCategories"
                            class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition"
                            title="Refresh">
                            <i class="pi pi-refresh"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-if="viewMode === 'list' && filteredCategories.length > 0">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Slug
                            </th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="cat in filteredCategories" :key="cat.id" class="hover:bg-gray-50 transition-colors"
                            :class="editSlug === cat.slug ? 'bg-blue-50' : ''">
                            <td class="py-4 px-6">
                                <span class="font-mono text-sm text-gray-600">#{{ cat.id }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="pi pi-folder text-gray-600"></i>
                                    </div>
                                    <span class="font-medium text-gray-900">{{ cat.name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                                    {{ cat.slug }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex gap-2">
                                    <button @click="editCategory(cat)"
                                        class="px-4 py-2 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 rounded-lg transition flex items-center gap-2"
                                        title="Edit">
                                        <i class="pi pi-pencil"></i>
                                        <span class="hidden sm:inline">Edit</span>
                                    </button>
                                    <button @click="deleteCategory(cat.slug)"
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
            <div v-else-if="viewMode === 'grid' && filteredCategories.length > 0" class="p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <div v-for="cat in filteredCategories" :key="cat.id"
                        class="border border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition-all duration-200 group"
                        :class="editSlug === cat.slug ? 'bg-blue-50 border-blue-300' : ''">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 bg-linear-to-br from-blue-50 to-blue-100 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i class="pi pi-folder text-blue-600 text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 truncate">{{ cat.name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1 font-mono">{{ cat.slug }}</p>
                                </div>
                            </div>
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="flex gap-1">
                                    <button @click="editCategory(cat)"
                                        class="w-8 h-8 flex items-center justify-center bg-white hover:bg-yellow-50 text-yellow-600 rounded-lg transition shadow-sm"
                                        title="Edit">
                                        <i class="pi pi-pencil text-sm"></i>
                                    </button>
                                    <button @click="deleteCategory(cat.slug)"
                                        class="w-8 h-8 flex items-center justify-center bg-white hover:bg-red-50 text-red-600 rounded-lg transition shadow-sm"
                                        title="Delete">
                                        <i class="pi pi-trash text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="pi pi-inbox text-gray-400 text-3xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-700 mb-2">No categories found</h4>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    {{
                        searchQuery
                            ? 'No categories match your search. Try different keywords.'
                            : 'Start by adding your first category using the form above.'
                    }}
                </p>
                <button v-if="searchQuery" @click="searchQuery = ''"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition inline-flex items-center gap-2">
                    <i class="pi pi-times"></i>
                    Clear Search
                </button>
            </div>

            <!-- Footer -->
            <div v-if="filteredCategories.length > 0" class="px-5 py-3 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ filteredCategories.length }}</span>
                        of <span class="font-medium">{{ categories.length }}</span> categories
                        <span v-if="searchQuery" class="ml-2 text-gray-500">
                            • Filtered by "{{ searchQuery }}"
                        </span>
                    </div>
                    <div class="text-sm text-gray-500">
                        View: <span class="font-medium">{{ viewMode === 'list' ? 'List' : 'Grid' }}</span>
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
const API_URL = "http://pos.test/api/categories"

// State
const categories = ref([])
const categoryName = ref("")
const editMode = ref(false)
const editSlug = ref(null)
const searchQuery = ref("")
const viewMode = ref("grid") // 'list' or 'grid'

// Computed
const filteredCategories = computed(() => {
    if (!searchQuery.value) return categories.value
    const query = searchQuery.value.toLowerCase()
    return categories.value.filter(cat =>
        cat.name.toLowerCase().includes(query) ||
        cat.slug.toLowerCase().includes(query)
    )
})

// Methods
const fetchCategories = async () => {
    try {
        const res = await axios.get(API_URL)
        categories.value = res.data.data
    } catch (err) {
        showError('Failed to load categories')
    }
}

const addCategory = async () => {
    try {
        const res = await axios.post(API_URL, { name: categoryName.value })
        categories.value.push(res.data.data)
        categoryName.value = ""
        showSuccess('Category added successfully')
    } catch (err) {
        showError('Failed to add category')
    }
}

const editCategory = (cat) => {
    categoryName.value = cat.name
    editMode.value = true
    editSlug.value = cat.slug
}

const updateCategory = async () => {
    try {
        const res = await axios.put(`${API_URL}/${editSlug.value}`, { name: categoryName.value })
        const index = categories.value.findIndex(cat => cat.slug === editSlug.value)
        if (index !== -1) categories.value[index] = res.data.data
        cancelEdit()
        showSuccess('Category updated successfully')
    } catch (err) {
        showError('Failed to update category')
    }
}

const cancelEdit = () => {
    categoryName.value = ""
    editMode.value = false
    editSlug.value = null
}

const deleteCategory = async (slug) => {
    if (!confirm('Are you sure you want to delete this category?')) return

    try {
        await axios.delete(`${API_URL}/${slug}`)
        categories.value = categories.value.filter(cat => cat.slug !== slug)
        showSuccess('Category deleted successfully')
    } catch (err) {
        showError('Failed to delete category')
    }
}

// Toast helpers
const showSuccess = (message) => {
    toast.add({ severity: 'success', summary: 'Success', detail: message, life: 3000 })
}

const showError = (message) => {
    toast.add({ severity: 'error', summary: 'Error', detail: message, life: 4000 })
}

// Lifecycle
onMounted(fetchCategories)
</script>

<style scoped>
/* Custom scrollbar for table */
table {
    min-width: 100%;
}

/* Smooth transitions */
tr {
    transition: background-color 0.15s ease;
}

.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .grid-cols-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Focus styles */
button:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

input:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 0;
}
</style>