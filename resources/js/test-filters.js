// Simular una prueba básica del endpoint
const testEndpoint = async () => {
    try {
        // URL base del proyecto
        const baseUrl = 'http://pos.test'; // Cambiar según tu configuración

        let response = await fetch(`${baseUrl}/dynamic-dashboard/filter-options`);
        await response.json();

        response = await fetch(`${baseUrl}/dynamic-dashboard/filter-options?widget_type=sales_chart`);
        await response.json();
        response = await fetch(`${baseUrl}/dynamic-dashboard/filter-options?widget_type=top_products`);
        await response.json();

        response = await fetch(`${baseUrl}/dynamic-dashboard/filter-options?widget_type=low_stock`);
        await response.json();

    } catch (error) {
        console.error('Error en las pruebas:', error);
    }
};

// Ejecutar cuando el DOM esté listo
if (typeof window !== 'undefined') {
    // Agregar un botón para probar manualmente
    const button = document.createElement('button');
    button.textContent = 'Probar Filtros Contextuales';
    button.onclick = testEndpoint;
    button.style.cssText = 'position: fixed; top: 10px; right: 10px; z-index: 9999; padding: 10px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;';
    document.body.appendChild(button);
}

export { testEndpoint };
