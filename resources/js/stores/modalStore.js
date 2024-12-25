import { defineStore } from "pinia";

export const useModalStore = defineStore('modal', {
    state: () => ({
        currentModal: null,
        params: null
    }),

    actions: {
        openModal(modal, params = null){
            this.currentModal = modal;
            this.params = params
        },

        closeModal(){
            this.lastModal = this.currentModal;
            this.lastParams = this.params;

            this.currentModal = null;
            this.params = null;
        }
    }
})