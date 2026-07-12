<script setup>
import { ref, onMounted, computed } from 'vue';
import { useExpenseStore } from '@/stores/expense';
import { useToast } from 'primevue/usetoast';
import { useRoute, useRouter } from 'vue-router';

const store = useExpenseStore();
const toast = useToast();
const route = useRoute();
const router = useRouter();

const expenseId = computed(() => route.params.id || null);
const isEdit = computed(() => !!expenseId.value);

const form = ref({
    title: '',
    amount: '',
    expense_category_id: '',
    expense_date: '',
    note: '',
    attachment: null,
});

const previewAttachment = ref(null);
const attachmentFile = ref(null);
const attachmentInput = ref(null);

const today = new Date().toISOString().split('T')[0];

const loadCategories = async () => {
    await store.fetchCategories();
};

const loadExpense = async () => {
    const data = await store.fetchExpense(expenseId.value);
    Object.assign(form.value, {
        title: data.title,
        amount: data.amount,
        expense_category_id: data.category?.id ? String(data.category.id) : '',
        expense_date: data.expense_date,
        note: data.note || '',
    });
    if (data.attachment) {
        previewAttachment.value = data.attachment;
    }
};

const handleAttachment = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    attachmentFile.value = file;
    previewAttachment.value = URL.createObjectURL(file);
};

const removeAttachment = () => {
    attachmentFile.value = null;
    previewAttachment.value = null;
};

const save = async () => {
    const payload = {
        ...form.value,
        attachment: attachmentFile.value,
    };

    try {
        if (isEdit.value) {
            await store.updateExpense(expenseId.value, payload);
            toast.add({ severity: "success", summary: "Success", detail: "Expense updated successfully", life: 1800 });
        } else {
            await store.createExpense(payload);
            toast.add({ severity: "success", summary: "Success", detail: "Expense created successfully", life: 1800 });
        }
        router.push('/expenses');
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: err?.response?.data?.message || "Could not save expense",
            life: 2500,
        });
    }
};

onMounted(async () => {
    await loadCategories();
    if (isEdit.value) {
        try {
            await loadExpense();
        } catch (e) {
            router.push('/expenses');
        }
    } else {
        form.value.expense_date = today;
    }
});
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900">
            {{ isEdit ? 'Edit Expense' : 'Add Expense' }}
        </h1>

        <div class="flex gap-6">
            <div class="flex-1 bg-white border rounded-xl shadow p-6 space-y-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Expense Information</h2>

                <div>
                    <label class="block mb-1 font-medium">Title</label>
                    <input v-model="form.title" class="w-full p-3 bg-gray-50 border rounded-lg" placeholder="Expense title" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Amount (৳)</label>
                        <input v-model="form.amount" type="number" step="0.01" class="w-full p-3 bg-gray-50 border rounded-lg" />
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Expense Date</label>
                        <input v-model="form.expense_date" type="date" :max="today" class="w-full p-3 bg-gray-50 border rounded-lg" />
                    </div>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Category</label>
                    <select v-model="form.expense_category_id" class="w-full p-3 bg-gray-50 border rounded-lg">
                        <option value="">Select category</option>
                        <option v-for="cat in store.categories" :key="cat.id" :value="String(cat.id)">
                            {{ cat.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Note</label>
                    <textarea v-model="form.note" rows="3" class="w-full p-3 bg-gray-50 border rounded-lg" placeholder="Optional note"></textarea>
                </div>
            </div>

            <div class="w-80 space-y-6">
                <div class="bg-white border rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Attachment</h2>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-5 text-center cursor-pointer hover:bg-gray-50"
                        @click="attachmentInput.click()">
                        <input type="file" accept="image/*" ref="attachmentInput" class="hidden" @change="handleAttachment" />
                        <div v-if="!previewAttachment" class="flex flex-col items-center">
                            <i class="pi pi-image text-4xl text-gray-400"></i>
                            <p class="text-gray-600 mt-2">Click to upload receipt</p>
                        </div>
                        <div v-else class="relative flex justify-center">
                            <img :src="previewAttachment" class="w-40 h-40 object-cover rounded-lg shadow-md border" />
                            <button @click.stop="removeAttachment"
                                class="absolute -top-3 -right-3 bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center">
                                <i class="pi pi-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 flex justify-end gap-3">
            <button @click="router.push('/expenses')" class="px-5 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                Cancel
            </button>
            <button @click="save" :disabled="store.loading"
                class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {{ isEdit ? 'Update Expense' : 'Save Expense' }}
            </button>
        </div>
    </div>
</template>