<script setup>
import { ref, onMounted } from 'vue';
import { useExpenseStore } from '@/stores/expense';
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

const store = useExpenseStore();
const toast = useToast();
const router = useRouter();

const search = ref('');
const deleteModal = ref(false);
const selectedExpense = ref(null);

const fetchExpenses = async () => {
    try {
        await store.fetchExpenses({ search: search.value });
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Failed",
            detail: "Could not load expenses",
            life: 2500,
        });
    }
};

const confirmDelete = (expense) => {
    selectedExpense.value = expense;
    deleteModal.value = true;
};

const deleteExpense = async () => {
    try {
        await store.deleteExpense(selectedExpense.value.id);
        toast.add({
            severity: "success",
            summary: "Deleted",
            detail: "Expense deleted successfully",
            life: 1500,
        });
        deleteModal.value = false;
        selectedExpense.value = null;
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to delete expense",
            life: 2000,
        });
    }
};

const onSearch = () => {
    fetchExpenses();
};

onMounted(fetchExpenses);
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Manage Expenses</h1>
            <Button label="Add Expense" icon="pi pi-plus" @click="router.push('/expenses/create')" />
        </div>

        <div class="bg-white border rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Expense List</h2>
                <span class="p-input-icon-left">
                    <InputText v-model="search" placeholder="Search expenses..." @input="onSearch" />
                </span>
            </div>

            <DataTable :value="store.expenses" stripedRows paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]"
                tableStyle="min-width: 50rem;" :loading="store.loading">
                <Column field="title" header="Title"></Column>
                <Column header="Category">
                    <template #body="{ data }">
                        {{ data.category?.name || '—' }}
                    </template>
                </Column>
                <Column header="Amount">
                    <template #body="{ data }">
                        ৳ {{ Number(data.amount).toFixed(2) }}
                    </template>
                </Column>
                <Column field="expense_date" header="Date"></Column>
                <Column header="Actions">
                    <template #body="{ data }">
                        <div class="flex items-center gap-3">
                            <button @click="router.push(`/expenses/${data.id}/edit`)"
                                class="px-3 py-1.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm">
                                Edit
                            </button>
                            <button @click="confirmDelete(data)"
                                class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                                Delete
                            </button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="deleteModal" modal header="Confirm Delete" class="w-full md:w-96">
            <p class="text-gray-700">
                Are you sure you want to delete
                <strong>{{ selectedExpense?.title }}</strong>?
            </p>
            <div class="flex justify-end gap-3 mt-6">
                <button class="px-4 py-2 bg-gray-200 rounded-lg" @click="deleteModal = false">
                    Cancel
                </button>
                <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700" @click="deleteExpense">
                    Yes, Delete
                </button>
            </div>
        </Dialog>
    </div>
</template>