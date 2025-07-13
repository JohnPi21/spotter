import { defineStore } from "pinia";

export const useUserStore = defineStore("user", {
    state: () => ({
        user: null,
    }),

    actions: {
        setUser(userData) {
            this.user = userData;
        },
    },
});

// export const useUserStore = defineStore('user', () => {
//     const user = ref(null);

//     function setUser(userData){
//         user.value = userData;
//     }

//     return {user, setUser}
// })
