<script setup>
import { ref, onMounted, nextTick } from "vue";
import { useToast } from "primevue/usetoast";
import { useApi } from "@/composables/useApi";
import { useRoute, useRouter } from "vue-router";
import Editor from "primevue/editor";

const api = useApi();
const router = useRouter();
const route = useRoute();
const toast = useToast();

const productSlug = route.params.slug;

// FORM
const form = ref({
  name: "",
  category_id: "",
  brand_id: "",
  buying_price: "",
  selling_price: "",
  stock_in: "",
  low_stock_quantity: "",
  description: "",
  sku: ""
});

const categories = ref([]);
const brands = ref([]);

const existingThumbnail = ref(null);
const previewImage = ref(null);
const imageFile = ref(null);
const thumbInput = ref(null);

const loadData = async () => {
  try {
    // Load dropdown data first
    const catRes = await api.get("/categories");
    categories.value = catRes.data.data;

    const brandRes = await api.get("/brands");
    brands.value = brandRes.data.data;

    await nextTick();

    // Load product
    const res = await api.get(`/products/${productSlug}`);
    const p = res.data.data;

    // Map correct fields from your API response
    Object.assign(form.value, {
      name: p.name,
      category_id: String(p.category?.id ?? ""),
      brand_id: p.brand ? String(p.brand.id) : "",
      buying_price: p.pricing.buying_price,
      selling_price: p.pricing.selling_price,
      stock_in: p.stock.stock_in,
      low_stock_quantity: p.stock.low_stock_quantity,
      description: p.description,
      sku: p.sku
    });

    existingThumbnail.value = p.thumbnail_url || null;

  } catch (err) {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Failed to load product",
      life: 2500,
    });
    router.push("/products");
  }
};

const handleImageUpload = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  imageFile.value = file;
  previewImage.value = URL.createObjectURL(file);
};

const handleDrop = (e) => {
  const file = e.dataTransfer.files[0];
  if (!file) return;
  imageFile.value = file;
  previewImage.value = URL.createObjectURL(file);
};

const removeThumbnail = () => {
  previewImage.value = null;
  imageFile.value = null;
  existingThumbnail.value = null;
};

const updateProduct = async () => {
  try {
    const fd = new FormData();

    Object.keys(form.value).forEach((key) => {
      fd.append(key, form.value[key]);
    });

    if (imageFile.value) {
      fd.append("thumbnail", imageFile.value);
    }

    fd.append("_method", "PUT");

    await api.post(`/products/${productSlug}`, fd, {
      headers: { "Content-Type": "multipart/form-data" }
    });

    toast.add({
      severity: "success",
      summary: "Success",
      detail: "Product updated successfully",
      life: 1800,
    });

    router.push("/products");
  } catch (err) {
    toast.add({
      severity: "error",
      summary: "Failed",
      detail: "Could not update product",
      life: 2500,
    });
  }
};

onMounted(loadData);
</script>


<template>
  <div class="space-y-6">

    <!-- HEADER -->
    <h1 class="text-2xl font-bold">Edit Product</h1>

    <div class="flex gap-6">

      <!-- LEFT FORM -->
      <div class="flex-1 bg-white border rounded-xl shadow p-6 space-y-6">
        <h2 class="text-lg font-semibold">Product Information</h2>

        <!-- NAME -->
        <div>
          <label class="block mb-1 font-medium">Product Name</label>
          <input v-model="form.name" class="w-full p-3 bg-gray-50 border rounded-lg" />
        </div>

        <!-- CATEGORY + BRAND -->
        <div class="grid grid-cols-2 gap-4">

          <div>
            <label class="block mb-1 font-medium">Category</label>
            <select v-model="form.category_id" class="w-full p-3 bg-gray-50 border rounded-lg">
              <option value="">Select category</option>
              <option v-for="cat in categories" :key="cat.id" :value="String(cat.id)">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block mb-1 font-medium">Brand</label>
            <select v-model="form.brand_id" class="w-full p-3 bg-gray-50 border rounded-lg">
              <option value="">Select brand</option>
              <option v-for="b in brands" :key="b.id" :value="String(b.id)">
                {{ b.name }}
              </option>
            </select>
          </div>

        </div>

        <!-- PRICES -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium">Buying Price</label>
            <input v-model="form.buying_price" type="number" class="w-full p-3 bg-gray-50 border rounded-lg" />
          </div>
          <div>
            <label class="block mb-1 font-medium">Selling Price</label>
            <input v-model="form.selling_price" type="number" class="w-full p-3 bg-gray-50 border rounded-lg" />
          </div>
        </div>

        <!-- STOCK -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium">Initial Stock</label>
            <input v-model="form.stock_in" type="number" class="w-full p-3 bg-gray-50 border rounded-lg" />
          </div>
          <div>
            <label class="block mb-1 font-medium">Low Stock Alert</label>
            <input v-model="form.low_stock_quantity" type="number" class="w-full p-3 bg-gray-50 border rounded-lg" />
          </div>
        </div>

        <!-- DESCRIPTION -->
        <div>
          <label class="block mb-1 font-medium">Description</label>
          <Editor v-model="form.description" editorStyle="height:220px" class="border rounded-lg bg-white" />
        </div>
      </div>

      <!-- RIGHT SIDEBAR -->
      <div class="w-80 space-y-6">

        <!-- THUMBNAIL -->
        <div class="bg-white border rounded-xl shadow p-6">
          <h2 class="text-lg font-semibold mb-4">Thumbnail</h2>

          <div
            class="border-2 border-dashed border-gray-300 rounded-xl p-5 text-center cursor-pointer hover:bg-gray-50"
            @click="thumbInput.click()"
            @dragover.prevent
            @drop.prevent="handleDrop"
          >
            <input type="file" ref="thumbInput" class="hidden" accept="image/*" @change="handleImageUpload" />

            <!-- Existing -->
            <div v-if="existingThumbnail && !previewImage">
              <img :src="existingThumbnail" class="w-40 h-40 object-cover rounded-lg mx-auto" />
              <button @click.stop="removeThumbnail" class="mt-3 px-3 py-1 bg-red-600 text-white rounded-lg text-sm">
                Remove
              </button>
            </div>

            <!-- Preview -->
            <div v-else-if="previewImage" class="relative flex justify-center">
              <img :src="previewImage" class="w-40 h-40 object-cover rounded-lg" />
              <button
                @click.stop="removeThumbnail"
                class="absolute -top-3 -right-3 bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center"
              >
                ✕
              </button>
            </div>

            <!-- Empty -->
            <div v-else>
              <i class="pi pi-image text-4xl text-gray-400"></i>
              <p class="text-gray-600 mt-2">Click or drag & drop</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="p-4 flex justify-end gap-3">
      <button @click="router.push('/products')" class="px-5 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
        Cancel
      </button>

      <button @click="updateProduct" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        Update Product
      </button>
    </div>

  </div>
</template>
