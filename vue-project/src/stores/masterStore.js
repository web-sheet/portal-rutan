import { defineStore } from 'pinia'
import api from "@/api/axios"

export const useMasterStore = defineStore('master', {
  state: () => ({
    items: [],
    loading: false
  }),

  actions: {
    async fetchItems(endpoint) {
      this.loading = true
      try {
        const response = await api.get(endpoint)
        // Menangani jika respons langsung berupa Array atau didalam response.data.data
        this.items = Array.isArray(response.data) ? response.data : (response.data.data || [])
      } catch (error) {
        console.error('Error fetching data:', error)
      } finally {
        this.loading = false
      }
    },

    async createItem(endpoint, payload) {
      try {
        const response = await api.post(endpoint, payload)
        const newItem = response.data.data || response.data
        this.items.push(newItem)
        return response.data
      } catch (error) {
        console.error('Error creating item:', error)
        throw error
      }
    },

    async updateItem(endpoint, id, payload) {
      try {
        const response = await api.put(`${endpoint}/${id}`, payload)
        const updatedItem = response.data.data || response.data
        
        const index = this.items.findIndex(item => item.id == id)
        if (index !== -1) {
          this.items[index] = updatedItem
        }
        return response.data
      } catch (error) {
        console.error('Error updating item:', error)
        throw error
      }
    },

    async deleteItem(endpoint, id) {
      try {
        await api.delete(`${endpoint}/${id}`)
        this.items = this.items.filter(item => item.id != id)
      } catch (error) {
        console.error('Error deleting item:', error)
        throw error
      }
    }
  }
})