const ordersList = document.getElementById('ordersList');
const ordersEmpty = document.getElementById('ordersEmpty');
const ordersStatus = document.getElementById('ordersStatus');
const ordersUserLabel = document.getElementById('ordersUserLabel');
const navToggle = document.getElementById('navToggle');
const navMenu = document.getElementById('navMenu');
const themeToggle = document.getElementById('themeToggle');
const savedTheme = localStorage.getItem('campuscart-theme');
const canUseApi = window.location.protocol === 'http:' || window.location.protocol === 'https:';
const currentUser = JSON.parse(localStorage.getItem('campuscart-current-user') || 'null');
const localOrders = JSON.parse(localStorage.getItem('campuscart-orders') || '[]');

function formatPrice(value) {
  return `Rs. ${Number(value).toFixed(2)}`;
}

function formatDate(value) {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return value || 'Unknown date';
  }
  return date.toLocaleString();
}

function updateStatus(message, isConnected) {
  ordersStatus.textContent = message;
  ordersStatus.classList.toggle('database-note--success', !!isConnected);
  ordersStatus.classList.toggle('database-note--warning', !isConnected);
}

function renderOrders(orders) {
  ordersList.innerHTML = orders.map((order) => `
    <article class="order-card reveal visible">
      <div class="order-card__head">
        <div>
          <p class="order-card__label">Order #${order.order_id || order.id}</p>
          <h3>${order.customer_name || order.name || 'Customer Order'}</h3>
        </div>
        <span class="order-status">${order.order_status || 'Processing'}</span>
      </div>
      <div class="order-card__grid">
        <div>
          <strong>Email</strong>
          <p>${order.customer_email || order.email || '-'}</p>
        </div>
        <div>
          <strong>Phone</strong>
          <p>${order.customer_phone || order.phone || '-'}</p>
        </div>
        <div>
          <strong>Total</strong>
          <p>${formatPrice(order.total_amount || order.total || 0)}</p>
        </div>
        <div>
          <strong>Date</strong>
          <p>${formatDate(order.order_date || order.created_at)}</p>
        </div>
      </div>
      <div class="order-card__address">
        <strong>Shipping Address</strong>
        <p>${order.shipping_address || order.address || '-'}</p>
      </div>
      ${(order.items && order.items.length)
        ? `<div class="order-card__items">
            <strong>Items</strong>
            <ul>
              ${order.items.map((item) => `<li>${item.name} x ${item.quantity}</li>`).join('')}
            </ul>
          </div>`
        : ''
      }
    </article>
  `).join('');

  ordersEmpty.classList.toggle('hidden', orders.length > 0);
}

function loadDemoOrders() {
  const filtered = currentUser?.email
    ? localOrders.filter((order) => order.email === currentUser.email)
    : localOrders;

  if (currentUser?.email) {
    ordersUserLabel.textContent = `Showing orders for ${currentUser.email}`;
  } else {
    ordersUserLabel.textContent = 'No logged-in customer found. Showing browser-saved demo orders.';
  }

  updateStatus('Running in demo mode. Orders are loading from browser storage.', false);
  renderOrders(filtered);
}

async function loadOrders() {
  if (!canUseApi) {
    loadDemoOrders();
    return;
  }

  const email = currentUser?.email || '';
  if (!email) {
    loadDemoOrders();
    return;
  }

  try {
    const response = await fetch(`api/orders.php?email=${encodeURIComponent(email)}`);
    if (!response.ok) {
      throw new Error('Unable to load orders.');
    }

    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Unable to load orders.');
    }

    ordersUserLabel.textContent = `Showing orders for ${email}`;

    if (result.database) {
      updateStatus('Orders loaded from MySQL successfully.', true);
      renderOrders(result.orders || []);
    } else {
      loadDemoOrders();
    }
  } catch (error) {
    loadDemoOrders();
  }
}

if (savedTheme === 'dark') {
  document.body.classList.add('dark-theme');
}

if (navToggle && navMenu) {
  navToggle.addEventListener('click', () => {
    const isOpen = navMenu.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', String(isOpen));
  });
}

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-theme');
    const mode = document.body.classList.contains('dark-theme') ? 'dark' : 'light';
    localStorage.setItem('campuscart-theme', mode);
  });
}

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

loadOrders();
