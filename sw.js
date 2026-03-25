/* Este archivo debe estar colocado en la carpeta raíz del sitio. */

const VERSION = "3.5.3"; // Incrementamos para forzar actualización
const CACHE = "pwamd";
const URL_SERVIDOR = "https://ganttasticos3-github-io.onrender.com";

const ARCHIVOS = [
  "ayuda.html",
  "favicon.ico",
  "index.html",
  "site.webmanifest",

  /* CSS */
  "css/baseline.css",
  "css/colors.css",
  "css/elevation.css",
  "css/estilos.css",
  "css/material-symbols-outlined.css",
  "css/md-filled-button.css",
  "css/md-filled-text-field.css",
  "css/md-headline.css",
  "css/md-list.css",
  "css/md-menu.css",
  "css/md-outline-button.css",
  "css/md-tab.css",
  "css/motion.css",
  "css/palette.css",
  "css/roboto.css",
  "css/shape.css",
  "css/state.css",
  "css/transicion_pestanas.css",
  "css/typography.css",
  "css/theme/dark.css",
  "css/theme/light.css",

  /* ERRORES */
  "errors/authtokenincorrecto.html",
  "errors/contentencodingincorrecta.html",
  "errors/datosnojson.html",
  "errors/endpointincorrecto.html",
  "errors/errorinterno.html",
  "errors/publickeyincorrecta.html",
  "errors/resultadonojson.html",

  /* FONTS */
  "fonts/MaterialSymbolsOutlined[FILL,GRAD,opsz,wght].codepoints",
  "fonts/MaterialSymbolsOutlined[FILL,GRAD,opsz,wght].ttf",
  "fonts/MaterialSymbolsOutlined[FILL,GRAD,opsz,wght].woff2",
  "fonts/roboto-v32-latin-regular.woff2",

  /* IMÁGENES */
  "img/BALTA.png",
  "img/HECTOR.png",
  "img/ITATI.png",
  "img/MENDIETA.png",
  "img/ROBER.png",
  "img/Vanne.png",
  "img/icono2048.png",
  "img/maskable_icon.png",
  "img/maskable_icon_x128.png",
  "img/maskable_icon_x192.png",
  "img/maskable_icon_x384.png",
  "img/maskable_icon_x48.png",
  "img/maskable_icon_x512.png",
  "img/maskable_icon_x72.png",
  "img/maskable_icon_x96.png",
  "img/Screenshot_horizontal_1.jpeg",
  "img/Screenshot_vertical_1.jpeg",
  "img/Screenshot_horizontal_2.jpeg",
  "img/Screenshot_vertical_2.jpeg",
"img/Escritorio.png",
  /* JS */
  "js/nav-tab-fixed.js",
  "js/lib/abreElementoHtml.js",
  "js/lib/activaNotificacionesPush.js",
  "js/lib/calculaDtoParaSuscripcion.js",
  "js/lib/cancelaSuscripcionPush.js",
  "js/lib/cierraElementoHtmo.js",
  "js/lib/consume.js",
  "js/lib/descargaVista.js",
  "js/lib/enviaJsonRecibeJson.js",
  "js/lib/ES_APPLE.js",
  "js/lib/getAttribute.js",
  "js/lib/getSuscripcionPush.js",
  "js/lib/manejaErrores.js",
  "js/lib/muestraError.js",
  "js/lib/muestraObjeto.js",
  "js/lib/muestraTextoDeAyuda.js",
  "js/lib/ProblemDetailsError.js",
  "js/lib/querySelector.js",
  "js/lib/recibeJson.js",
  "js/lib/registraServiceWorker.js",
  "js/lib/resaltaSiEstasEn.js",
  "js/lib/suscribeAPush.js",
  "js/lib/urlBase64ToUint8Array.js",

  /* CUSTOM */
  "js/lib/custom/md-app-bar.js",
  "js/lib/custom/md-options-menu.js",
  "js/lib/custom/md-select-menu.js",

  /* POLYFILL */
  "ungap/custom-elements.js",

  /* ROOT */
  "/",
  "sw.js",
];

// ==============================
// CICLO DE VIDA (SERVICE WORKER)
// ==============================

if (self instanceof ServiceWorkerGlobalScope) {
  self.addEventListener("install", (evt) => {
    console.log("SW: Instalando y cargando caché...");
    // Fuerza al SW a saltar la espera y activarse de inmediato
    self.skipWaiting();
    evt.waitUntil(llenaElCache());
  });

  self.addEventListener("activate", (evt) => {
    console.log("SW: Activo y tomando control.");
    // Toma control de las pestañas abiertas inmediatamente
    evt.waitUntil(self.clients.claim());
  });

  self.addEventListener("fetch", (evt) => {
    if (evt.request.method === "GET") {
      evt.respondWith(buscaEnCache(evt));
    }
  });
}

// ==============================
// 💾 GESTIÓN DE CACHÉ
// ==============================

async function llenaElCache() {
  // Limpiamos versiones anteriores para no llenar el disco
  const keys = await caches.keys();
  for (const key of keys) {
    await caches.delete(key);
  }

  const cache = await caches.open(CACHE);

  for (const archivo of ARCHIVOS) {
    try {
      await cache.add(archivo);
    } catch (e) {
      console.error("No se pudo cachear:", archivo);
    }
  }
  console.log("Cache listo (v" + VERSION + ")");
}

async function buscaEnCache(evt) {
  const cache = await caches.open(CACHE);
  const response = await cache.match(evt.request, { ignoreSearch: true });
  // Si está en caché lo devuelve, si no, lo busca en internet
  return response || fetch(evt.request);
}

// ==============================
// 🔔 PUSH NOTIFICATIONS
// ==============================

self.addEventListener("push", (event) => {
  console.log("🔥 Push recibido");

  let data = {
    title: "Notificación",
    body: "Nuevo mensaje 👀",
  };

  if (event.data) {
    try {
      data = event.data.json();
    } catch {
      data.body = event.data.text();
    }
  }

  event.waitUntil(
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: "img/icono2048.png",
      badge: "img/icono2048.png",
      vibrate: [100, 50, 100],
      data: {
        url: URL_SERVIDOR,
      },
    })
  );
});

// ==============================
// 🖱 CLICK EN NOTIFICACIÓN
// ==============================

self.addEventListener("notificationclick", (event) => {
  event.notification.close();

  event.waitUntil(
    self.clients.matchAll({ type: "window" }).then((clientes) => {
      for (const cliente of clientes) {
        if (cliente.url.includes(URL_SERVIDOR)) {
          return cliente.focus();
        }
      }
      return self.clients.openWindow("/");
    })
  );
});