const fallbackProducts = [
  {
    id: 1,
    name: 'Classic Black Tee',
    category: 'T-Shirts',
    description: 'Relaxed premium cotton t-shirt with a clean streetwear silhouette.',
    price: 599,
    stock: 12,
    image: 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=900&q=80'
  },
  {
    id: 2,
    name: 'Studio White Tee',
    category: 'T-Shirts',
    description: 'Minimal white tee for everyday styling and layered fits.',
    price: 399,
    stock: 18,
    image: 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?auto=format&fit=crop&w=900&q=80'
  },
  {
    id: 3,
    name: 'Scarlet Flow Dress',
    category: 'Dresses',
    description: 'Elegant statement dress designed for modern events and evening wear.',
    price: 999,
    stock: 7,
    image: 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=900&q=80'
  },
  {
    id: 4,
    name: 'Urban Layer Jacket',
    category: 'Outerwear',
    description: 'Structured jacket with a clean cut and transitional styling.',
    price: 1899,
    stock: 10,
    image: 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80'
  },
  {
    id: 5,
    name: 'Soft Knit Set',
    category: 'Women',
    description: 'Contemporary knitwear set balancing comfort and editorial appeal.',
    price: 1599,
    stock: 8,
    image: 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=900&q=80'
  },
  {
    id: 6,
    name: 'Tailored Essentials',
    category: 'Men',
    description: 'Refined casualwear with relaxed tailoring and premium drape.',
    price: 1299,
    stock: 14,
    image: 'https://images.unsplash.com/photo-1516826957135-700dedea698c?auto=format&fit=crop&w=900&q=80'
  }
];

const navToggle = document.getElementById('navToggle');
const navMenu = document.getElementById('navMenu');
const themeToggle = document.getElementById('themeToggle');
const savedTheme = localStorage.getItem('campuscart-theme');
const productGrid = document.getElementById('productGrid');
const cartContent = document.getElementById('cartContent');
const summaryCount = document.getElementById('summaryCount');
const summaryTotal = document.getElementById('summaryTotal');
const navCartCount = document.getElementById('navCartCount');
const emptyState = document.getElementById('emptyState');
const toast = document.getElementById('toast');
const searchForm = document.getElementById('searchForm');
const searchInput = document.getElementById('searchInput');
const databaseStatus = document.getElementById('databaseStatus');
const categoryFilters = document.getElementById('categoryFilters');
const sortSelect = document.getElementById('sortSelect');
const minPriceRange = document.getElementById('minPriceRange');
const maxPriceRange = document.getElementById('maxPriceRange');
const minPriceLabel = document.getElementById('minPriceLabel');
const maxPriceLabel = document.getElementById('maxPriceLabel');
const canUseApi = window.location.protocol === 'http:' || window.location.protocol === 'https:';

let products = [];
let cart = JSON.parse(localStorage.getItem('campuscart-cart') || '[]');
let activeCategory = 'All';
let currentSearch = '';
let localUsers = JSON.parse(localStorage.getItem('campuscart-users') || '[]');
let localOrders = JSON.parse(localStorage.getItem('campuscart-orders') || '[]');

const formatPrice = (value) => `Rs. ${Number(value).toFixed(2)}`;

function showToast(message) {
  toast.textContent = message;
  toast.classList.add('show');
  window.setTimeout(() => toast.classList.remove('show'), 2200);
}

function saveCart() {
  localStorage.setItem('campuscart-cart', JSON.stringify(cart));
}

function saveLocalUsers() {
  localStorage.setItem('campuscart-users', JSON.stringify(localUsers));
}

function saveLocalOrders() {
  localStorage.setItem('campuscart-orders', JSON.stringify(localOrders));
}

function updateDatabaseStatus(message, isConnected) {
  databaseStatus.textContent = message;
  databaseStatus.classList.toggle('database-note--success', !!isConnected);
  databaseStatus.classList.toggle('database-note--warning', !isConnected);
}

function syncPriceLabels() {
  let minValue = Number(minPriceRange.value);
  let maxValue = Number(maxPriceRange.value);

  if (minValue > maxValue) {
    [minValue, maxValue] = [maxValue, minValue];
    minPriceRange.value = String(minValue);
    maxPriceRange.value = String(maxValue);
  }

  minPriceLabel.textContent = formatPrice(minValue);
  maxPriceLabel.textContent = formatPrice(maxValue);
}

function buildCategoryFilters() {
  const categories = ['All', ...new Set(products.map((product) => product.category || 'Uncategorized'))];
  categoryFilters.innerHTML = categories.map((category) => `
    <label class="category-chip">
      <input type="radio" name="category" value="${category}" ${category === activeCategory ? 'checked' : ''}>
      <span>${category}</span>
    </label>
  `).join('');
}

function visibleProducts() {
  const minPrice = Math.min(Number(minPriceRange.value), Number(maxPriceRange.value));
  const maxPrice = Math.max(Number(minPriceRange.value), Number(maxPriceRange.value));

  let filtered = products.filter((product) => {
    const matchesCategory = activeCategory === 'All' || (product.category || 'Uncategorized') === activeCategory;
    const matchesPrice = Number(product.price) >= minPrice && Number(product.price) <= maxPrice;
    return matchesCategory && matchesPrice;
  });

  const searchTerm = currentSearch.trim().toLowerCase();
  if (searchTerm) {
    filtered = filtered.filter((product) => [product.name, product.category, product.description].some((value) => String(value).toLowerCase().includes(searchTerm)));
  }

  const sortValue = sortSelect.value;
  if (sortValue === 'low-high') {
    filtered.sort((a, b) => Number(a.price) - Number(b.price));
  } else if (sortValue === 'high-low') {
    filtered.sort((a, b) => Number(b.price) - Number(a.price));
  } else if (sortValue === 'name') {
    filtered.sort((a, b) => String(a.name).localeCompare(String(b.name)));
  }

  return filtered;
}

function renderProducts(items) {
  productGrid.innerHTML = items.map((product) => `
    <article class="product-card reveal visible">
      <div class="product-card__image">
        <img src="${product.image}" alt="${product.name}">
        <span class="product-card__wish">?</span>
      </div>
      <div class="product-card__body">
        <div class="product-card__meta">
          <span>${product.category}</span>
          <span>Stock ${product.stock}</span>
        </div>
        <h3>${product.name}</h3>
        <div class="product-card__price">${formatPrice(product.price)}</div>
        <div class="product-card__actions">
          <button class="button button--primary" type="button" data-add-cart="${product.id}">Add to Cart</button>
          <button class="button button--ghost button--small" type="button">View</button>
        </div>
      </div>
    </article>
  `).join('');

  emptyState.classList.toggle('hidden', items.length > 0);
}

function renderCatalog() {
  syncPriceLabels();
  renderProducts(visibleProducts());
}

function renderCart() {
  if (!cart.length) {
    cartContent.innerHTML = `
      <div class="empty-state">
        <h3>Your cart is empty</h3>
        <p>Add products from the catalog to continue shopping.</p>
      </div>
    `;
    summaryCount.textContent = '0';
    summaryTotal.textContent = 'Rs. 0.00';
    navCartCount.textContent = '0';
    return;
  }

  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  const totalPrice = cart.reduce((sum, item) => sum + Number(item.price) * item.quantity, 0);

  cartContent.innerHTML = `
    <div class="cart-items">
      ${cart.map((item) => `
        <div class="cart-row">
          <div class="cart-row__details">
            <h4>${item.name}</h4>
            <p>${item.category}</p>
            <strong>${formatPrice(item.price)}</strong>
          </div>
          <div class="cart-row__actions">
            <input class="qty-input" type="number" min="1" value="${item.quantity}" data-qty-id="${item.id}">
            <button class="button button--ghost button--small" type="button" data-remove-id="${item.id}">Remove</button>
          </div>
        </div>
      `).join('')}
    </div>
  `;

  summaryCount.textContent = String(totalItems);
  summaryTotal.textContent = formatPrice(totalPrice);
  navCartCount.textContent = String(totalItems);
}

function addToCart(productId) {
  const product = products.find((item) => Number(item.id) === productId);
  if (!product) {
    return;
  }

  const existing = cart.find((item) => Number(item.id) === productId);
  if (existing) {
    existing.quantity += 1;
  } else {
    cart.push({ ...product, quantity: 1 });
  }

  saveCart();
  renderCart();
  showToast(`${product.name} added to cart.`);
}

function removeFromCart(productId) {
  cart = cart.filter((item) => Number(item.id) !== productId);
  saveCart();
  renderCart();
  showToast('Item removed from cart.');
}

function updateQuantity(productId, quantity) {
  const item = cart.find((entry) => Number(entry.id) === productId);
  if (!item) {
    return;
  }

  item.quantity = Math.max(1, quantity);
  saveCart();
  renderCart();
}

function normalizeProducts(list) {
  return list.map((product) => ({
    ...product,
    price: Number(product.price),
    stock: Number(product.stock),
    category: product.category || 'Uncategorized',
    image: product.image || 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=900&q=80'
  }));
}

function applyPriceBounds() {
  const prices = products.map((product) => Number(product.price));
  const min = prices.length ? Math.floor(Math.min(...prices)) : 0;
  const max = prices.length ? Math.ceil(Math.max(...prices)) : 10000;
  minPriceRange.min = String(min);
  minPriceRange.max = String(max);
  maxPriceRange.min = String(min);
  maxPriceRange.max = String(max);
  minPriceRange.value = String(min);
  maxPriceRange.value = String(max);
}

async function loadProducts(searchTerm = '') {
  currentSearch = searchTerm;

  if (!canUseApi) {
    products = normalizeProducts(fallbackProducts);
    applyPriceBounds();
    buildCategoryFilters();
    renderCatalog();
    updateDatabaseStatus('Open this project through XAMPP/WAMP localhost to fetch products from MySQL. Showing demo products for now.', false);
    return;
  }

  try {
    const url = searchTerm ? `api/products.php?q=${encodeURIComponent(searchTerm)}` : 'api/products.php';
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error('Unable to fetch products.');
    }

    const result = await response.json();
    products = normalizeProducts(Array.isArray(result.products) && result.products.length ? result.products : fallbackProducts);
    applyPriceBounds();
    buildCategoryFilters();
    renderCatalog();

    if (result.database) {
      updateDatabaseStatus('Products loaded from MySQL successfully.', true);
    } else {
      updateDatabaseStatus('Database not connected yet. Showing fallback demo products.', false);
    }
  } catch (error) {
    products = normalizeProducts(fallbackProducts);
    applyPriceBounds();
    buildCategoryFilters();
    renderCatalog();
    updateDatabaseStatus('API unavailable right now. Showing fallback demo products.', false);
  }
}

async function submitApiForm(form) {
  if (!canUseApi) {
    return submitLocalForm(form);
  }

  const endpoint = form.dataset.endpoint;
  const formData = Object.fromEntries(new FormData(form).entries());

  if (form.dataset.formName === 'Checkout') {
    formData.items = cart;
  }

  const response = await fetch(endpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(formData)
  });

  let result;
  try {
    result = await response.json();
  } catch (error) {
    return submitLocalForm(form);
  }

  if (!response.ok || !result.success) {
    if (response.status >= 500 || response.status === 0) {
      return submitLocalForm(form);
    }
    throw new Error(result.message || 'Request failed.');
  }

  return result;
}

function submitLocalForm(form) {
  const formData = Object.fromEntries(new FormData(form).entries());
  const formName = form.dataset.formName;

  if (formName === 'Registration') {
    const alreadyExists = localUsers.some((user) => user.email === formData.email);
    if (alreadyExists) {
      throw new Error('This email is already registered in local demo mode.');
    }

    localUsers.push({
      id: Date.now(),
      name: formData.name,
      email: formData.email,
      password: formData.password
    });
    saveLocalUsers();
    updateDatabaseStatus('Running in demo mode. Customer data is being stored in this browser.', false);
    return {
      success: true,
      message: 'Registration saved in demo mode.'
    };
  }

  if (formName === 'Login') {
    const user = localUsers.find((entry) => entry.email === formData.email && entry.password === formData.password);
    if (!user) {
      throw new Error('Invalid email or password in demo mode.');
    }

    localStorage.setItem(
      'campuscart-current-user',
      JSON.stringify({ id: user.id, name: user.name, email: user.email })
    );
    updateDatabaseStatus('Running in demo mode. Login is using browser-stored customer data.', false);
    return {
      success: true,
      message: `Welcome back, ${user.name}.`
    };
  }

  if (formName === 'Checkout') {
    if (!cart.length) {
      throw new Error('Your cart is empty.');
    }

    const order = {
      id: Date.now(),
      name: formData.name,
      email: formData.email,
      phone: formData.phone,
      address: formData.address,
      payment_method: formData.payment_method,
      items: cart,
      total: cart.reduce((sum, item) => sum + Number(item.price) * item.quantity, 0),
      created_at: new Date().toISOString()
    };

    localOrders.push(order);
    saveLocalOrders();
    updateDatabaseStatus('Running in demo mode. Booking data is being stored in this browser.', false);
    return {
      success: true,
      message: 'Order saved in demo mode.'
    };
  }

  throw new Error('Unsupported form action.');
}

if (savedTheme === 'dark') {
  document.body.classList.add('dark-theme');
}

if (navToggle && navMenu) {
  navToggle.addEventListener('click', () => {
    const isOpen = navMenu.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', String(isOpen));
  });

  navMenu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      navMenu.classList.remove('open');
      navToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-theme');
    const mode = document.body.classList.contains('dark-theme') ? 'dark' : 'light';
    localStorage.setItem('campuscart-theme', mode);
  });
}

searchForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  await loadProducts(searchInput.value.trim());
});

sortSelect.addEventListener('change', renderCatalog);
minPriceRange.addEventListener('input', renderCatalog);
maxPriceRange.addEventListener('input', renderCatalog);

categoryFilters.addEventListener('change', (event) => {
  const input = event.target.closest('input[name="category"]');
  if (!input) {
    return;
  }
  activeCategory = input.value;
  renderCatalog();
});

productGrid.addEventListener('click', (event) => {
  const button = event.target.closest('[data-add-cart]');
  if (!button) {
    return;
  }
  addToCart(Number(button.dataset.addCart));
});

cartContent.addEventListener('click', (event) => {
  const button = event.target.closest('[data-remove-id]');
  if (!button) {
    return;
  }
  removeFromCart(Number(button.dataset.removeId));
});

cartContent.addEventListener('change', (event) => {
  const input = event.target.closest('[data-qty-id]');
  if (!input) {
    return;
  }
  updateQuantity(Number(input.dataset.qtyId), Number(input.value));
});

document.querySelectorAll('.api-form').forEach((form) => {
  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
      if (form.dataset.formName === 'Checkout' && cart.length === 0) {
        showToast('Add products to the cart before checkout.');
        return;
      }

      const result = await submitApiForm(form);
      showToast(result.message || `${form.dataset.formName} successful.`);

      if (form.dataset.formName === 'Login' && result.user) {
        localStorage.setItem('campuscart-current-user', JSON.stringify(result.user));
      }

      if (form.dataset.formName === 'Registration') {
        localStorage.setItem(
          'campuscart-current-user',
          JSON.stringify({
            name: formDataName(form, 'name'),
            email: formDataName(form, 'email')
          })
        );
      }

      form.reset();

      if (form.dataset.formName === 'Checkout') {
        cart = [];
        saveCart();
        renderCart();
      }
    } catch (error) {
      showToast(error.message);
    }
  });
});

const revealElements = document.querySelectorAll('.reveal');

if ('IntersectionObserver' in window) {
  const revealObserver = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12 }
  );

  revealElements.forEach((element) => revealObserver.observe(element));
} else {
  revealElements.forEach((element) => element.classList.add('visible'));
}

loadProducts();
renderCart();

function formDataName(form, key) {
  return new FormData(form).get(key) || '';
}
