import { reactive, computed, watch } from 'vue';
import axios from 'axios';

// The actual cart data
const state = reactive({
    items: JSON.parse(localStorage.getItem('cart')) || []
});

export const useCart = () => {

    const playSound = (type) => {
        const audio = new Audio(type === 'add' ? '/sounds/add.mp3' : '/sounds/remove.mp3');
        audio.volume = 0.2;
        audio.play().catch(() => {}); // Catch block prevents errors if browser blocks autoplay
    };

    // Add item to cart
    const addToCart = (product) => {
        const existingItem = state.items.find(i => i.id === product.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            state.items.push({ ...product, quantity: 1 });
        }
        saveCart();
        playSound('add');
    };

    // Remove item
    const removeFromCart = (id) => {
        state.items = state.items.filter(i => i.id !== id);
        saveCart();
        playSound('remove');
    };

    // Total price calculation
    const total = computed(() => {
        return state.items.reduce((acc, item) => acc + (item.price * item.quantity), 0);
    });

    // Save to local storage so items stay if user refreshes
    const saveCart = () => {
        localStorage.setItem('cart', JSON.stringify(state.items));
    };

    const clearCart = () => {
        state.items = [];
        localStorage.removeItem('cart');
    };

    const updateQuantity = (id, amount) => {
        const item = state.items.find(i => i.id === id);
        if (item) {
            item.quantity += amount;
            if (item.quantity <= 0) {
                removeFromCart(id);
            }
        }
        saveCart();
    };

    watch(() => state.items, (newItems) => {
        localStorage.setItem('cart', JSON.stringify(newItems));
        
        if (newItems.length > 0) {
            axios.post(route('cart.sync'), { items: newItems })
                .catch(err => console.error("Cart sync failed", err));
        }
    }, { deep: true });

    return { 
        items: computed(() => state.items), 
        addToCart, 
        removeFromCart, 
        updateQuantity, // Export this
        total, 
        clearCart 
    };
};