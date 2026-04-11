export const trackViewedProduct = (product) => {
    let history = JSON.parse(localStorage.getItem('recent_views') || '[]');
    
    // Remove if already exists (to move it to the front)
    history = history.filter(item => item.id !== product.id);
    
    // Add to the beginning
    history.unshift(product);
    
    // Keep only the last 4
    history = history.slice(0, 4);
    
    localStorage.setItem('recent_views', JSON.stringify(history));
};