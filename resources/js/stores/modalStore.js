import { defineStore } from "pinia";

export const useModalStore = defineStore('modal', {
    state: () => ({
        currentModal: null,
        data: null,
        callback: null,

        lastModal: null,
        lastData: null,
        lastCallback: null,
    }),

    actions: {
        openModal(modal, data = null, callback = null) {
            this.currentModal = modal;
            this.data = data;
            this.callback = callback;
        },

        closeModal(callback_param) {
            this.lastModal = this.currentModal;
            this.lastData = this.data;
            this.lastCallback = this.callback;

            if (typeof this.callback === 'function') {
                this.callback(callback_param);
            }

            this.currentModal = null;
            this.data = null;
            this.callback = null;

        }
    }
})