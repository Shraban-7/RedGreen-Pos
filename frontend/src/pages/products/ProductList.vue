<script setup>
import { useApi } from '@/composables/useApi';
import { useToast } from 'primevue/usetoast';
import { ref, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const api = useApi();
const toast = useToast();

const products = ref([]);
const deleteModal = ref(false);
const selectedProduct = ref(null);

// FETCH PRODUCTS
const fetchProducts = async () => {
    try {
        const res = await api.get('/products');
        products.value = res.data.data;
    } catch (err) {
        console.error(err);
        toast.add({
            severity: "error",
            summary: "Failed",
            detail: "Could not load products",
            life: 2500,
        });
    }
};

// OPEN DELETE MODAL
const confirmDelete = (product) => {
    selectedProduct.value = product;
    deleteModal.value = true;
};

// DELETE PRODUCT
const deleteProduct = async () => {
    try {
        await api.delete(`/products/${selectedProduct.value.id}`);
        products.value = products.value.filter(p => p.id !== selectedProduct.value.id);

        toast.add({
            severity: "success",
            summary: "Deleted",
            detail: "Product deleted successfully",
            life: 1500,
        });

        deleteModal.value = false;
        selectedProduct.value = null;

    } catch (err) {
        console.error(err);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to delete product",
            life: 2000,
        });
    }
};

onMounted(fetchProducts);
</script>

<template>
    <div class="space-y-6">

        <!-- HEADER -->
        <div class="bg-white border rounded-xl shadow p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manage Products</h1>
                <p class="text-gray-500 text-sm mt-1">View and manage all products</p>
            </div>

            <button
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                @click="$router.push('/products/create')"
            >
                + Create Product
            </button>
        </div>

        <!-- PRODUCT TABLE -->
        <div class="bg-white border rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Product List</h2>

            <DataTable
                :value="products"
                stripedRows
                paginator
                :rows="10"
                :rowsPerPageOptions="[10, 20, 50]"
                tableStyle="min-width: 50rem;"
            >
                <Column field="sku" header="Code"></Column>
                <Column field="name" header="Name"></Column>
                <Column field="category.name" header="Category"></Column>

                <Column header="Quantity">
                    <template #body="{ data }">
                        {{ data.quantity ?? (data.stock_in - data.stock_out) }}
                    </template>
                </Column>

                <!-- ACTION BUTTONS -->
                <Column header="Actions">
                    <template #body="{ data }">
                        <div class="flex items-center gap-3">

                            <!-- EDIT -->
                            <button
                                @click="$router.push(`/products/${data.slug}/edit`)"
                                class="px-3 py-1.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm"
                            >
                                Edit
                            </button>

                            <!-- DELETE -->
                            <button
                                @click="confirmDelete(data)"
                                class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm"
                            >
                                Delete
                            </button>

                        </div>
                    </template>
                </Column>

            </DataTable>
        </div>

        <!-- DELETE CONFIRMATION MODAL -->
        <Dialog
            v-model:visible="deleteModal"
            modal
            header="Confirm Delete"
            class="w-full md:w-96"
        >
            <p class="text-gray-700">
                Are you sure you want to delete
                <strong>{{ selectedProduct?.name }}</strong>?
            </p>

            <div class="flex justify-end gap-3 mt-6">
                <button
                    class="px-4 py-2 bg-gray-200 rounded-lg"
                    @click="deleteModal = false"
                >
                    Cancel
                </button>

                <button
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    @click="deleteProduct"
                >
                    Yes, Delete
                </button>
            </div>
        </Dialog>

    </div>
</template>
