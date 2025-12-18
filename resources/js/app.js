import './bootstrap';
// Close all dropdowns when modal opens
document.addEventListener('DOMContentLoaded', function() {
    // Watch for modal changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                // Check if modal was added
                const modalAdded = Array.from(mutation.addedNodes).some(node => 
                    node.nodeType === 1 && (
                        node.classList?.contains('fi-modal') || 
                        node.querySelector?.('.fi-modal')
                    )
                );
                
                if (modalAdded) {
                    // Close all dropdowns
                    document.querySelectorAll('.fi-dropdown-panel').forEach(panel => {
                        panel.style.display = 'none';
                        panel.style.visibility = 'hidden';
                        panel.style.opacity = '0';
                    });
                    
                    // Trigger Alpine.js to close dropdowns
                    document.querySelectorAll('[x-data]').forEach(el => {
                        if (el._x_dataStack && el._x_dataStack[0] && el._x_dataStack[0].open) {
                            el._x_dataStack[0].open = false;
                        }
                    });
                }
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});