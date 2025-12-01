<template>
    <div class="p-6">
        <Toast />

        <h1 class="text-2xl font-bold text-indigo-500 mb-4">Categories</h1>

        <!-- Add / Edit Form -->
        <div class="mb-6 bg-gray-800 p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold text-white mb-2">
                {{ editMode ? 'Edit Category' : 'Add New Category' }}
            </h2>
            <form @submit.prevent="editMode ? updateCategory() : addCategory()" class="flex gap-2">
                <input type="text" v-model="categoryName" placeholder="Category Name"
                    class="flex-1 px-3 py-2 rounded-lg text-gray-900" required />
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg">
                    {{ editMode ? 'Update' : 'Add' }}
                </button>
                <button v-if="editMode" type="button" @click="cancelEdit"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Cancel
                </button>
            </form>
        </div>

        <!-- Categories Table -->
        <table class="w-full text-left text-gray-300">
            <thead class="border-b border-gray-600">
                <tr>
                    <th class="py-2 px-4">#</th>
                    <th class="py-2 px-4">Name</th>
                    <th class="py-2 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(cat, index) in categories" :key="cat.id" class="border-b border-gray-700">
                    <td class="py-2 px-4">{{ index + 1 }}</td>
                    <td class="py-2 px-4">{{ cat.name }}</td>
                    <td class="py-2 px-4 flex gap-2">
                        <button @click="editCategory(cat)"
                            class="bg-yellow-500 hover:bg-yellow-600 px-2 py-1 rounded text-white">
                            Edit
                        </button>
                        <button @click="deleteCategory(cat.slug)"
                            class="bg-red-500 hover:bg-red-600 px-2 py-1 rounded text-white">
                            Delete
                        </button>
                    </td>
                </tr>
                <tr v-if="categories.length === 0">
                    <td colspan="3" class="text-center py-4 text-gray-500">No categories found.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import { useToast } from 'primevue/usetoast'

const toast = useToast() 

const categories = ref([])
const categoryName = ref("")
const editMode = ref(false)
const editSlug = ref(null)

const API_URL = "http://pos.test/api/categories"

const fetchCategories = async () => {
    try {
        const res = await axios.get(API_URL)
        categories.value = res.data.data
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to fetch categories' })
    }
}

const addCategory = async () => {
    try {
        const res = await axios.post(API_URL, { name: categoryName.value })
        categories.value.push(res.data.data)
        categoryName.value = ""
        toast.add({ severity: 'success', summary: 'Success', detail: 'Category added' })
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to add category' })
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
        toast.add({ severity: 'success', summary: 'Success', detail: 'Category updated' })
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to update category' })
    }
}

const cancelEdit = () => {
    categoryName.value = ""
    editMode.value = false
    editSlug.value = null
}

const deleteCategory = async (slug) => {
    try {
        await axios.delete(`${API_URL}/${slug}`)
        categories.value = categories.value.filter(cat => cat.slug !== slug)
        toast.add({ severity: 'success', summary: 'Success', detail: 'Category deleted' })
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete category' })
    }
}

onMounted(fetchCategories)
</script>

<style scoped>
table th,
table td {
    vertical-align: middle;
}
</style>
