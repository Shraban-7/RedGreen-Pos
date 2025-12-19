<script setup>
import { reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'

import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Card from 'primevue/card'

const auth = useAuthStore()
const router = useRouter()
const toast = useToast()

const loading = ref(false)

const form = reactive({
  login: '',
  password: '',
})

const submit = async () => {
  loading.value = true

  try {
    await auth.login(form)
    toast.add({
      severity: 'success',
      summary: 'Login Successful',
      detail: 'Welcome back',
      life: 3000,
    })
    router.push('/')
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Login Failed',
      detail: error?.response?.data?.message || 'Invalid credentials',
      life: 3000,
    })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex justify-center items-center min-h-screen bg-gray-100">
    <Card class="w-full max-w-md">
      <template #title>Login</template>

      <template #content>
        <form @submit.prevent="submit" class="flex flex-col gap-4">
          <div>
            <label class="block mb-1 font-medium">
              Email / Username / Phone
            </label>
            <InputText
              v-model="form.login"
              class="w-full"
              placeholder="Enter your login"
              required
            />
          </div>

          <div>
            <label class="block mb-1 font-medium">Password</label>
            <Password
              v-model="form.password"
              class="w-full"
              inputClass="w-full"
              :feedback="false"
              toggleMask
              required
            />
          </div>

          <Button
            type="submit"
            label="Login"
            icon="pi pi-sign-in"
            :loading="loading"
            class="w-full"
          />
        </form>
      </template>
    </Card>
  </div>
</template>
