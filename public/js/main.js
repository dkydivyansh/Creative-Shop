/**
 * Main JavaScript file for the E-Commerce Store Frontend
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- START: Added Mobile Detection ---
/**
 * Checks if the current device is likely a mobile device.
 * It checks for a small screen width or the presence of touch capabilities.
 * @returns {boolean} True if it's likely a mobile device, otherwise false.
 */
const isMobileDevice = () => {
    // 768px is a common breakpoint for tablets.
    // You can adjust this value based on your needs.
    const isSmallScreen = window.innerWidth <= 768;

    // This is a reliable way to check for touch support.
    const hasTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    return isSmallScreen || hasTouch;
};

    // --- 1. Smooth Scrolling with Lenis ---
    if (typeof Lenis !== 'undefined') {
        const lenis = new Lenis({
            duration: 1.2,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        });

        function raf(time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        }
        requestAnimationFrame(raf);
    }

    // --- 2. Mobile Menu Functionality ---
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    const menuBackdrop = document.querySelector('.menu-backdrop');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');

    if (mobileMenuToggle && mobileMenu && menuBackdrop && mobileMenuClose) {
        const toggleMenu = () => {
            mobileMenu.classList.toggle('open');
            menuBackdrop.classList.toggle('open');
            document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
        };

        mobileMenuToggle.addEventListener('click', toggleMenu);
        menuBackdrop.addEventListener('click', toggleMenu);
        mobileMenuClose.addEventListener('click', toggleMenu);
    }
    
    // --- 3. Search Popup Functionality ---
const searchIcon = document.querySelector('.search-icon');
const searchPopup = document.querySelector('.search-popup');
const searchInput = document.querySelector('.search-input');

if (searchIcon && searchPopup) {
    const openSearch = () => {
        searchPopup.classList.add('open');
        document.body.style.overflow = 'hidden';
        if (searchInput) searchInput.focus();
    };

    const closeSearch = () => {
        searchPopup.classList.remove('open');
        document.body.style.overflow = '';
    };

    searchIcon.addEventListener('click', openSearch);

    searchPopup.addEventListener('click', (e) => {
        if (e.target === searchPopup) closeSearch();
    });

    // 🔹 Trigger search on Enter key (desktop + mobile keyboards)
    if (searchInput) {
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = searchInput.value.trim();
                if (query) {
                    window.location.href = `/search?q=${encodeURIComponent(query)}`;
                }
            }
        });
    }
}


    // --- 4. Dynamic Category Filtering (AJAX) ---
    const filterTabs = document.querySelectorAll('.filter-tab');
    const productsGrid = document.querySelector('.products-grid');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent page reload

            const category = this.dataset.category;
            
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            fetchProducts(category);
            history.pushState(null, '', this.href);
        });
    });

    async function fetchProducts(category) {
        try {
            productsGrid.innerHTML = '<p style="grid-column: 1 / -1; text-align: center;">Loading products...</p>';
            const response = await fetch(`/api.php?action=get_products&category=${category}`);
            if (!response.ok) throw new Error('Network response was not ok');
            const products = await response.json();
            renderProducts(products);
        } catch (error) {
            console.error('Failed to fetch products:', error);
            productsGrid.innerHTML = '<p style="grid-column: 1 / -1; text-align: center;">Failed to load products. Please try again.</p>';
        }
    }

    function renderProducts(products) {
        productsGrid.innerHTML = '';
        if (products.length === 0) {
            productsGrid.innerHTML = '<p style="grid-column: 1 / -1; text-align: center;">No products found in this category.</p>';
            return;
        }

        products.forEach(product => {
            const price = parseFloat(product.price);
            const discount = parseInt(product.discount, 10);
            let priceHTML = `₹${price.toFixed(2)}`;

            if (discount > 0) {
                const discountedPrice = price - (price * discount / 100);
                priceHTML = `<del style="color: #666; margin-right: 8px;">₹${price.toFixed(2)}</del> ₹${discountedPrice.toFixed(2)}`;
            }

            const productCard = `
                <a href="/${product.sku}" class="product-card">
                    <div class="product-image">
                        <img src="${product.image || '/public/images/preholder3.gif'}" alt="${product.name}">
                    </div>
                    <div class="product-info">
                        <div class="product-details">
                            <h3 class="product-name">${product.name}</h3>
                            <p class="product-category">${product.category_name || 'Uncategorized'}</p>
                        </div>
                        <div class="product-price">${priceHTML}</div>
                    </div>
                </a>
            `;
            productsGrid.insertAdjacentHTML('beforeend', productCard);
        });
    }


    // --- 5. OGL Prism Background Animation ---
    const prismContainer = document.querySelector('.prism-background-container');
    if (prismContainer && window.ogl && !isMobileDevice()) {
    // OGL animation code from your template remains the same...
    const { Renderer, Program, Triangle, Mesh } = window.ogl;
    const config = { height: 3.5, baseWidth: 5.5, animationType: "hover", glow: 1.0, noise: 0.0, transparent: true, scale: 2, hueShift: 0.0, colorFrequency: 1.0, hoverStrength: 2.0, inertia: 0.05, bloom: 1.0, timeScale: 0.5 };
    const H = Math.max(0.001, config.height), BW = Math.max(0.001, config.baseWidth), BASE_HALF = BW * 0.5, GLOW = Math.max(0.0, config.glow), NOISE = Math.max(0.0, config.noise), SAT = config.transparent ? 1.5 : 1, SCALE = Math.max(0.001, config.scale), HUE = config.hueShift || 0, CFREQ = Math.max(0.0, config.colorFrequency || 1), BLOOM = Math.max(0.0, config.bloom || 1), TS = Math.max(0, config.timeScale || 1), HOVSTR = Math.max(0, config.hoverStrength || 1), INERT = Math.max(0, Math.min(1, config.inertia || 0.12));
    const dpr = Math.min(2, window.devicePixelRatio || 1);
    const renderer = new Renderer({ dpr, alpha: config.transparent, antialias: false });
    const gl = renderer.gl;
    gl.disable(gl.DEPTH_TEST); gl.disable(gl.CULL_FACE); gl.disable(gl.BLEND);
    Object.assign(gl.canvas.style, { position: "absolute", inset: "0", width: "100%", height: "100%", display: "block" });
    prismContainer.appendChild(gl.canvas);
    const vertex = `attribute vec2 position; void main() { gl_Position = vec4(position, 0.0, 1.0); }`;
    const fragment = `
        precision highp float;
        uniform vec2 iResolution; uniform float iTime; uniform float uHeight; uniform float uBaseHalf; uniform mat3 uRot; uniform int uUseBaseWobble; uniform float uGlow; uniform float uNoise; uniform float uSaturation; uniform float uScale; uniform float uHueShift; uniform float uColorFreq; uniform float uBloom; uniform float uCenterShift; uniform float uInvBaseHalf; uniform float uInvHeight; uniform float uMinAxis; uniform float uPxScale; uniform float uTimeScale;
        vec4 tanh4(vec4 x){ vec4 e2x = exp(2.0*x); return (e2x - 1.0) / (e2x + 1.0); }
        float rand(vec2 co){ return fract(sin(dot(co, vec2(12.9898, 78.233))) * 43758.5453123); }
        float sdOctaAnisoInv(vec3 p){ vec3 q = vec3(abs(p.x) * uInvBaseHalf, abs(p.y) * uInvHeight, abs(p.z) * uInvBaseHalf); float m = q.x + q.y + q.z - 1.0; return m * uMinAxis * 0.5773502691896258; }
        float sdPyramidUpInv(vec3 p){ float oct = sdOctaAnisoInv(p); float halfSpace = -p.y; return max(oct, halfSpace); }
        mat3 hueRotation(float a){ float c = cos(a), s = sin(a); mat3 W = mat3(0.299, 0.587, 0.114, 0.299, 0.587, 0.114, 0.299, 0.587, 0.114); mat3 U = mat3(0.701, -0.587, -0.114, -0.299, 0.413, -0.114, -0.300, -0.588, 0.886); mat3 V = mat3(0.168, -0.331, 0.500, 0.328, 0.035, -0.500, -0.497, 0.296, 0.201); return W + U * c + V * s; }
        void main(){
            vec2 f = (gl_FragCoord.xy - 0.5 * iResolution.xy) * uPxScale;
            float z = 5.0; float d = 0.0; vec3 p; vec4 o = vec4(0.0);
            float centerShift = uCenterShift; float cf = uColorFreq;
            mat2 wob = mat2(1.0);
            if (uUseBaseWobble == 1) { float t = iTime * uTimeScale; float c0 = cos(t + 0.0); float c1 = cos(t + 33.0); float c2 = cos(t + 11.0); wob = mat2(c0, c1, c2, c0); }
            const int STEPS = 100;
            for (int i = 0; i < STEPS; i++) {
                p = vec3(f, z); p.xz = p.xz * wob; p = uRot * p; vec3 q = p; q.y += centerShift;
                d = 0.1 + 0.2 * abs(sdPyramidUpInv(q)); z -= d;
                o += (sin((p.y + z) * cf + vec4(0.0, 1.0, 2.0, 3.0)) + 1.0) / d;
            }
            o = tanh4(o * o * (uGlow * uBloom) / 1e5);
            vec3 col = o.rgb; float n = rand(gl_FragCoord.xy + vec2(iTime)); col += (n - 0.5) * uNoise; col = clamp(col, 0.0, 1.0);
            float L = dot(col, vec3(0.2126, 0.7152, 0.0722)); col = clamp(mix(vec3(L), col, uSaturation), 0.0, 1.0);
            if(abs(uHueShift) > 0.0001){ col = clamp(hueRotation(uHueShift) * col, 0.0, 1.0); }
            gl_FragColor = vec4(col, o.a);
        }
    `;
    const geometry = new Triangle(gl);
    const iResBuf = new Float32Array(2);
    const program = new Program(gl, { vertex, fragment, uniforms: { iResolution: { value: iResBuf }, iTime: { value: 0 }, uHeight: { value: H }, uBaseHalf: { value: BASE_HALF }, uUseBaseWobble: { value: 1 }, uRot: { value: new Float32Array([1, 0, 0, 0, 1, 0, 0, 0, 1]) }, uGlow: { value: GLOW }, uNoise: { value: NOISE }, uSaturation: { value: SAT }, uScale: { value: SCALE }, uHueShift: { value: HUE }, uColorFreq: { value: CFREQ }, uBloom: { value: BLOOM }, uCenterShift: { value: H * 0.25 }, uInvBaseHalf: { value: 1 / BASE_HALF }, uInvHeight: { value: 1 / H }, uMinAxis: { value: Math.min(BASE_HALF, H) }, uPxScale: { value: 1 / ((gl.drawingBufferHeight || 1) * 0.1 * SCALE) }, uTimeScale: { value: TS }, } });
    const mesh = new Mesh(gl, { geometry, program });
    const resize = () => { const w = prismContainer.clientWidth || 1; const h = prismContainer.clientHeight || 1; renderer.setSize(w, h); iResBuf[0] = gl.drawingBufferWidth; iResBuf[1] = gl.drawingBufferHeight; program.uniforms.uPxScale.value = 1 / ((gl.drawingBufferHeight || 1) * 0.1 * SCALE); };
    const ro = new ResizeObserver(resize); ro.observe(prismContainer); resize();
    const rotBuf = new Float32Array(9);
    const setMat3FromEuler = (yawY, pitchX, rollZ, out) => { const cy = Math.cos(yawY), sy = Math.sin(yawY); const cx = Math.cos(pitchX), sx = Math.sin(pitchX); const cz = Math.cos(rollZ), sz = Math.sin(rollZ); out[0] = cy * cz + sy * sx * sz; out[1] = cx * sz; out[2] = -sy * cz + cy * sx * sz; out[3] = -cy * sz + sy * sx * cz; out[4] = cx * cz; out[5] = sy * sz + cy * sx * cz; out[6] = sy * cx; out[7] = -sx; out[8] = cy * cx; return out; };
    let raf = 0;
    const t0 = performance.now();
    const startRAF = () => { if (raf) return; raf = requestAnimationFrame(render); };
    let yaw = 0, pitch = 0, roll = 0; let targetYaw = 0, targetPitch = 0;
    const lerp = (a, b, t) => a + (b - a) * t;
    const pointer = { x: 0, y: 0, inside: true };
    if (config.animationType === "hover") {
        program.uniforms.uUseBaseWobble.value = 0;
        const onPointerMove = (e) => { const ww = Math.max(1, window.innerWidth); const wh = Math.max(1, window.innerHeight); const cx = ww * 0.5; const cy = wh * 0.5; const nx = (e.clientX - cx) / (ww * 0.5); const ny = (e.clientY - cy) / (wh * 0.5); pointer.x = Math.max(-1, Math.min(1, nx)); pointer.y = Math.max(-1, Math.min(1, ny)); pointer.inside = true; startRAF(); };
        const onMouseLeave = () => { pointer.inside = false; };
        window.addEventListener("pointermove", onPointerMove, { passive: true });
        window.addEventListener("mouseleave", onMouseLeave);
        window.addEventListener("blur", onMouseLeave);
    } else { program.uniforms.uUseBaseWobble.value = 1; }
    const render = (t) => {
        const time = (t - t0) * 0.001;
        program.uniforms.iTime.value = time;
        let continueRAF = true;
        if (config.animationType === "hover") {
            const maxPitch = 0.6 * HOVSTR; const maxYaw = 0.6 * HOVSTR;
            targetYaw = (pointer.inside ? -pointer.x : 0) * maxYaw;
            targetPitch = (pointer.inside ? pointer.y : 0) * maxPitch;
            yaw = lerp(yaw, targetYaw, INERT); pitch = lerp(pitch, targetPitch, INERT); roll = lerp(roll, 0, 0.1);
            program.uniforms.uRot.value = setMat3FromEuler(yaw, pitch, roll, rotBuf);
            const settled = Math.abs(yaw - targetYaw) < 1e-4 && Math.abs(pitch - targetPitch) < 1e-4 && Math.abs(roll) < 1e-4;
            if (settled && NOISE < 1e-6) continueRAF = false;
        }
        renderer.render({ scene: mesh });
        if (continueRAF) { raf = requestAnimationFrame(render); } else { raf = 0; }
    };
    startRAF();
};

});
