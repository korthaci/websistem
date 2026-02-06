// Google Maps Async Loader - Optimized Version
(function() {
    'use strict';
    
    let mapsLoaded = false;
    let mapsLoading = false;
    
    // Özel harita stilleri
    const mapStyles = {
        dark: [
            { elementType: "geometry", stylers: [{ color: "#242f3e" }] },
            { elementType: "labels.text.stroke", stylers: [{ color: "#242f3e" }] },
            { elementType: "labels.text.fill", stylers: [{ color: "#746855" }] },
            {
                featureType: "administrative.locality",
                elementType: "labels.text.fill",
                stylers: [{ color: "#d59563" }]
            },
            {
                featureType: "poi",
                elementType: "labels.text.fill",
                stylers: [{ color: "#d59563" }]
            },
            {
                featureType: "poi.park",
                elementType: "geometry",
                stylers: [{ color: "#263c3f" }]
            },
            {
                featureType: "poi.park",
                elementType: "labels.text.fill",
                stylers: [{ color: "#6b9a76" }]
            },
            {
                featureType: "road",
                elementType: "geometry",
                stylers: [{ color: "#38414e" }]
            },
            {
                featureType: "road",
                elementType: "geometry.stroke",
                stylers: [{ color: "#212a37" }]
            },
            {
                featureType: "road",
                elementType: "labels.text.fill",
                stylers: [{ color: "#9ca5b3" }]
            },
            {
                featureType: "road.highway",
                elementType: "geometry",
                stylers: [{ color: "#746855" }]
            },
            {
                featureType: "road.highway",
                elementType: "geometry.stroke",
                stylers: [{ color: "#1f2835" }]
            },
            {
                featureType: "road.highway",
                elementType: "labels.text.fill",
                stylers: [{ color: "#f3d19c" }]
            },
            {
                featureType: "transit",
                elementType: "geometry",
                stylers: [{ color: "#2f3948" }]
            },
            {
                featureType: "transit.station",
                elementType: "labels.text.fill",
                stylers: [{ color: "#d59563" }]
            },
            {
                featureType: "water",
                elementType: "geometry",
                stylers: [{ color: "#17263c" }]
            },
            {
                featureType: "water",
                elementType: "labels.text.fill",
                stylers: [{ color: "#515c6d" }]
            },
            {
                featureType: "water",
                elementType: "labels.text.stroke",
                stylers: [{ color: "#17263c" }]
            }
        ],
        
        soft: [
            { elementType: "geometry", stylers: [{ saturation: -20 }, { lightness: 15 }] },
            { elementType: "labels.text.fill", stylers: [{ color: "#5a5a5a" }] },
            { elementType: "labels.text.stroke", stylers: [{ color: "#ffffff" }, { weight: 2 }] },
            {
                featureType: "water",
                elementType: "geometry",
                stylers: [{ color: "#b8d4f1" }, { saturation: -10 }]
            },
            {
                featureType: "water",
                elementType: "labels.text.fill",
                stylers: [{ color: "#4a6f8a" }]
            },
            {
                featureType: "road",
                elementType: "geometry",
                stylers: [{ color: "#f8f8f8" }, { lightness: 20 }]
            },
            {
                featureType: "road",
                elementType: "labels.text.fill",
                stylers: [{ color: "#666666" }]
            },
            {
                featureType: "road.highway",
                elementType: "geometry",
                stylers: [{ color: "#e8e8e8" }, { saturation: -30 }]
            },
            {
                featureType: "road.highway",
                elementType: "labels.text.fill",
                stylers: [{ color: "#4a4a4a" }]
            },
            {
                featureType: "poi",
                elementType: "geometry",
                stylers: [{ saturation: -40 }, { lightness: 20 }]
            },
            {
                featureType: "poi",
                elementType: "labels.text.fill",
                stylers: [{ color: "#7a7a7a" }]
            },
            {
                featureType: "poi.park",
                elementType: "geometry",
                stylers: [{ color: "#c8e6c9" }, { saturation: -20 }]
            },
            {
                featureType: "landscape",
                elementType: "geometry",
                stylers: [{ saturation: -30 }, { lightness: 25 }]
            },
            {
                featureType: "administrative",
                elementType: "labels.text.fill",
                stylers: [{ color: "#666666" }]
            }
        ],
        
        mavi: [
            { elementType: "geometry", stylers: [{ color: "#e3f2fd" }] },
            { elementType: "labels.text.fill", stylers: [{ color: "#1565c0" }] },
            { elementType: "labels.text.stroke", stylers: [{ color: "#ffffff" }, { weight: 2 }] },
            {
                featureType: "water",
                elementType: "geometry",
                stylers: [{ color: "#2196f3" }]
            },
            {
                featureType: "water",
                elementType: "labels.text.fill",
                stylers: [{ color: "#0d47a1" }]
            },
            {
                featureType: "road",
                elementType: "geometry",
                stylers: [{ color: "#bbdefb" }]
            },
            {
                featureType: "road",
                elementType: "labels.text.fill",
                stylers: [{ color: "#1976d2" }]
            },
            {
                featureType: "road.highway",
                elementType: "geometry",
                stylers: [{ color: "#90caf9" }]
            },
            {
                featureType: "road.highway",
                elementType: "labels.text.fill",
                stylers: [{ color: "#0d47a1" }]
            },
            {
                featureType: "poi",
                elementType: "geometry",
                stylers: [{ color: "#c5e1fd" }]
            },
            {
                featureType: "poi",
                elementType: "labels.text.fill",
                stylers: [{ color: "#1565c0" }]
            },
            {
                featureType: "poi.park",
                elementType: "geometry",
                stylers: [{ color: "#81c784" }]
            },
            {
                featureType: "landscape",
                elementType: "geometry",
                stylers: [{ color: "#f1f8ff" }]
            },
            {
                featureType: "administrative",
                elementType: "labels.text.fill",
                stylers: [{ color: "#1565c0" }]
            },
            {
                featureType: "transit",
                elementType: "geometry",
                stylers: [{ color: "#b3e5fc" }]
            }
        ],
        
        yeşil: [
            { elementType: "geometry", stylers: [{ color: "#e8f5e8" }] },
            { elementType: "labels.text.fill", stylers: [{ color: "#2e7d32" }] },
            { elementType: "labels.text.stroke", stylers: [{ color: "#ffffff" }, { weight: 2 }] },
            {
                featureType: "water",
                elementType: "geometry",
                stylers: [{ color: "#4fc3f7" }]
            },
            {
                featureType: "water",
                elementType: "labels.text.fill",
                stylers: [{ color: "#0277bd" }]
            },
            {
                featureType: "road",
                elementType: "geometry",
                stylers: [{ color: "#c8e6c9" }]
            },
            {
                featureType: "road",
                elementType: "labels.text.fill",
                stylers: [{ color: "#388e3c" }]
            },
            {
                featureType: "road.highway",
                elementType: "geometry",
                stylers: [{ color: "#a5d6a7" }]
            },
            {
                featureType: "road.highway",
                elementType: "labels.text.fill",
                stylers: [{ color: "#1b5e20" }]
            },
            {
                featureType: "poi",
                elementType: "geometry",
                stylers: [{ color: "#dcedc8" }]
            },
            {
                featureType: "poi",
                elementType: "labels.text.fill",
                stylers: [{ color: "#2e7d32" }]
            },
            {
                featureType: "poi.park",
                elementType: "geometry",
                stylers: [{ color: "#66bb6a" }]
            },
            {
                featureType: "landscape",
                elementType: "geometry",
                stylers: [{ color: "#f1f8e9" }]
            },
            {
                featureType: "administrative",
                elementType: "labels.text.fill",
                stylers: [{ color: "#2e7d32" }]
            },
            {
                featureType: "transit",
                elementType: "geometry",
                stylers: [{ color: "#c8e6c9" }]
            }
        ],
        
        retro: [
            { elementType: "geometry", stylers: [{ color: "#ebe3cd" }] },
            { elementType: "labels.text.fill", stylers: [{ color: "#523735" }] },
            { elementType: "labels.text.stroke", stylers: [{ color: "#f5f1e6" }] },
            {
                featureType: "administrative",
                elementType: "geometry.stroke",
                stylers: [{ color: "#c9b2a6" }]
            },
            {
                featureType: "administrative.land_parcel",
                elementType: "geometry.stroke",
                stylers: [{ color: "#dcd2be" }]
            },
            {
                featureType: "administrative.land_parcel",
                elementType: "labels.text.fill",
                stylers: [{ color: "#ae9e90" }]
            },
            {
                featureType: "landscape.natural",
                elementType: "geometry",
                stylers: [{ color: "#dfd2ae" }]
            },
            {
                featureType: "poi",
                elementType: "geometry",
                stylers: [{ color: "#dfd2ae" }]
            },
            {
                featureType: "poi",
                elementType: "labels.text.fill",
                stylers: [{ color: "#93817c" }]
            },
            {
                featureType: "poi.park",
                elementType: "geometry.fill",
                stylers: [{ color: "#a5b076" }]
            },
            {
                featureType: "poi.park",
                elementType: "labels.text.fill",
                stylers: [{ color: "#447530" }]
            },
            {
                featureType: "road",
                elementType: "geometry",
                stylers: [{ color: "#f5f1e6" }]
            },
            {
                featureType: "road.arterial",
                elementType: "geometry",
                stylers: [{ color: "#fdfcf8" }]
            },
            {
                featureType: "road.highway",
                elementType: "geometry",
                stylers: [{ color: "#f8c967" }]
            },
            {
                featureType: "road.highway",
                elementType: "geometry.stroke",
                stylers: [{ color: "#e9bc62" }]
            },
            {
                featureType: "road.highway.controlled_access",
                elementType: "geometry",
                stylers: [{ color: "#e98d58" }]
            },
            {
                featureType: "road.highway.controlled_access",
                elementType: "geometry.stroke",
                stylers: [{ color: "#db8555" }]
            },
            {
                featureType: "road.local",
                elementType: "labels.text.fill",
                stylers: [{ color: "#806b63" }]
            },
            {
                featureType: "transit.line",
                elementType: "geometry",
                stylers: [{ color: "#dfd2ae" }]
            },
            {
                featureType: "transit.line",
                elementType: "labels.text.fill",
                stylers: [{ color: "#8f7d77" }]
            },
            {
                featureType: "transit.line",
                elementType: "labels.text.stroke",
                stylers: [{ color: "#ebe3cd" }]
            },
            {
                featureType: "transit.station",
                elementType: "geometry",
                stylers: [{ color: "#dfd2ae" }]
            },
            {
                featureType: "water",
                elementType: "geometry.fill",
                stylers: [{ color: "#b9d3c2" }]
            },
            {
                featureType: "water",
                elementType: "labels.text.fill",
                stylers: [{ color: "#92998d" }]
            }
        ]
    };
    
    function waitForElementVisible(element, timeout = 5000) {
        return new Promise((resolve, reject) => {
            if (element.offsetWidth > 0 && element.offsetHeight > 0) {
                resolve(true);
                return;
            }
            
            let timeoutId;
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && entry.boundingClientRect.width > 0) {
                        observer.disconnect();
                        if (timeoutId) clearTimeout(timeoutId);
                        resolve(true);
                    }
                });
            }, { threshold: 0.1 });
            
            observer.observe(element);
            
            timeoutId = setTimeout(() => {
                observer.disconnect();
                resolve(false);
            }, timeout);
        });
    }
    
    function loadGoogleMapsAPI() {
        return new Promise((resolve, reject) => {
            if (window.google && window.google.maps) {
                mapsLoaded = true;
                resolve();
                return;
            }
            
            if (mapsLoading) {
                const checkInterval = setInterval(() => {
                    if (mapsLoaded) {
                        clearInterval(checkInterval);
                        resolve();
                    }
                }, 100);
                return;
            }
            
            mapsLoading = true;
            
            const script = document.createElement('script');
            script.async = true;
            script.defer = true;
            
            window.initGoogleMaps = function() {
                mapsLoaded = true;
                mapsLoading = false;
                resolve();
            };
            
            script.onerror = function() {
                mapsLoading = false;
                reject(new Error('Google Maps API yüklenemedi'));
            };
            
            const googleMapsElement = document.getElementById('googlemaps');
            const apiKey = googleMapsElement ? googleMapsElement.getAttribute('data-key') : '';
            
            if (!apiKey) {
                reject(new Error('Google Maps API anahtarı bulunamadı'));
                return;
            }
            
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=initGoogleMaps&loading=async&libraries=marker`;
            document.head.appendChild(script);
        });
    }
    
    async function initializeMap() {
        const mapElement = document.getElementById('googlemaps');
        
        if (!mapElement) {
            return;
        }
        
        const centerLat = parseFloat(mapElement.getAttribute('data-center-lat')) || 41.0048046;
        const centerLng = parseFloat(mapElement.getAttribute('data-center-lng')) || 39.7267915;
        const mapAddress = mapElement.getAttribute('data-map-adres') || 'Adres bilgisi bulunamadı';
        const markerTitle = mapElement.getAttribute('data-marker-title') || 'Konum İşaretçisi';
        const markerIcon = mapElement.getAttribute('data-marker-icon') || '';
        const markerSize = parseInt(mapElement.getAttribute('data-marker-size')) || 32;
        const pinPulse = mapElement.getAttribute('data-pin-pulse') === '1';
        const mapZoom = parseInt(mapElement.getAttribute('data-zoom')) || 15;
        const mapType = mapElement.getAttribute('data-map-type') || 'roadmap';
        const mapStyle = mapElement.getAttribute('data-map-style') || '';
        const mapId = mapElement.getAttribute('data-map-id') || 'google_map_' + Date.now();
        const showControls = mapElement.getAttribute('data-show-controls') !== 'false';
        const showInfoWindow = mapElement.getAttribute('data-show-info') !== 'false';
        const autoOpenInfo = mapElement.getAttribute('data-auto-open-info') !== 'false';
        const allowScroll = mapElement.getAttribute('data-scroll') === '1';
        
        await waitForElementVisible(mapElement);
        
        const mapOptions = {
            zoom: mapZoom,
            center: { lat: centerLat, lng: centerLng },
            mapTypeId: google.maps.MapTypeId[mapType.toUpperCase()] || google.maps.MapTypeId.ROADMAP,
            scrollwheel: allowScroll,
            draggable: true,
            mapTypeControl: showControls,
            streetViewControl: showControls,
            fullscreenControl: showControls,
            zoomControl: showControls,
            gestureHandling: allowScroll ? 'auto' : 'cooperative'
        };
        
        const forceAdvancedMarker = pinPulse || (markerIcon && markerIcon.trim() !== '');
        
        if (forceAdvancedMarker) {
            mapOptions.mapId = mapId;
        } else if (mapStyle && mapStyles[mapStyle]) {
            mapOptions.styles = mapStyles[mapStyle];
        } else {
            mapOptions.mapId = mapId;
        }
        
        const map = new google.maps.Map(mapElement, mapOptions);
        
        let marker;
        const useAdvancedMarker = forceAdvancedMarker || (!mapStyle || !mapStyles[mapStyle]);
        
        if (google.maps.marker && google.maps.marker.AdvancedMarkerElement && useAdvancedMarker) {
            try {
                if (pinPulse) {
                    const pulseElement = document.createElement('div');
                    pulseElement.className = 'gmap-pulse-container';
                    
                    if (markerIcon && markerIcon.trim() !== '') {
                        pulseElement.innerHTML = `
                            <img src="${markerIcon}" style="
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                width: ${markerSize}px;
                                height: ${markerSize}px;
                                object-fit: contain;
                                z-index: 2;
                            ">
                            <div class="gmap-pulse-ring"></div>
                        `;
                    } else {
                        pulseElement.innerHTML = `
                            <div class="gmap-pulse-dot"></div>
                            <div class="gmap-pulse-ring"></div>
                        `;
                    }
                    
                    marker = new google.maps.marker.AdvancedMarkerElement({
                        position: { lat: centerLat, lng: centerLng },
                        map: map,
                        title: markerTitle,
                        content: pulseElement
                    });
                } else if (markerIcon && markerIcon.trim() !== '') {
                    const iconImg = document.createElement('img');
                    iconImg.src = markerIcon;
                    iconImg.style.width = `${markerSize}px`;
                    iconImg.style.height = `${markerSize}px`;
                    iconImg.style.objectFit = 'contain';
                    
                    marker = new google.maps.marker.AdvancedMarkerElement({
                        position: { lat: centerLat, lng: centerLng },
                        map: map,
                        title: markerTitle,
                        content: iconImg
                    });
                } else {
                    marker = new google.maps.marker.AdvancedMarkerElement({
                        position: { lat: centerLat, lng: centerLng },
                        map: map,
                        title: markerTitle
                    });
                }
            } catch (error) {
                useAdvancedMarker = false;
            }
        } 
        
        if (!useAdvancedMarker || !(google.maps.marker && google.maps.marker.AdvancedMarkerElement)) {
           
            const originalWarn = console.warn;
            console.warn = function(message) {
                if (typeof message === 'string' && message.includes('google.maps.Marker is deprecated')) {
                    return;
                }
                originalWarn.apply(console, arguments);
            };
            
            const markerOptions = {
                position: { lat: centerLat, lng: centerLng },
                map: map,
                title: markerTitle,
                animation: google.maps.Animation.DROP
            };
            
            if (markerIcon && markerIcon.trim() !== '' && !pinPulse) {
                markerOptions.icon = {
                    url: markerIcon,
                    scaledSize: new google.maps.Size(markerSize, markerSize),
                    anchor: new google.maps.Point(markerSize / 2, markerSize)
                };
            }
            
            marker = new google.maps.Marker(markerOptions);
            
            setTimeout(() => {
                console.warn = originalWarn;
            }, 100);
        }
        
        if (showInfoWindow) {
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 12px; max-width: 280px; font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.4;">
                        <div style="font-weight: 600; margin-bottom: 8px; color: #1a1a1a; font-size: 15px;">
                            🏛️ ${mapAddress}
                        </div>
                        <div style="margin: 8px 0; color: #666; font-size: 13px;">
                            ${markerTitle}
                        </div>
                        <div style="margin-top: 12px; border-top: 1px solid #eee; padding-top: 8px;">
                            <a href="https://www.google.com/maps/dir/?api=1&destination=${centerLat},${centerLng}" 
                               target="_blank" 
                               style="color: #1a73e8; text-decoration: none; font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px; outline: none;"
                               tabindex="-1">
                                📍 Yol Tarifi Al
                            </a>
                        </div>
                    </div>
                `,
                maxWidth: 300
            });
            
            const isAdvancedMarker = marker instanceof google.maps.marker?.AdvancedMarkerElement;
            
            if (isAdvancedMarker) {
                marker.addEventListener('click', () => {
                    infoWindow.open(map, marker);
                });
            } else {
                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });
            }
            
            if (autoOpenInfo) {
                setTimeout(() => {
                    infoWindow.open(map, marker);
                    setTimeout(() => {
                        const infoWindowLinks = document.querySelectorAll('.gm-style-iw a');
                        infoWindowLinks.forEach(link => link.blur());
                    }, 100);
                }, 300);
            }
        }
        
        return new Promise((resolve) => {
            google.maps.event.addListenerOnce(map, 'tilesloaded', () => {
                setTimeout(() => {
                    google.maps.event.trigger(map, 'resize');
                    map.setCenter({ lat: centerLat, lng: centerLng });
                }, 100);
                resolve(map);
            });
            
            setTimeout(() => {
                google.maps.event.trigger(map, 'resize');
                map.setCenter({ lat: centerLat, lng: centerLng });
                resolve(map);
            }, 3000);
        });
    }
    
    async function startGoogleMapsWithRetry(maxRetries = 3) {
        for (let attempt = 1; attempt <= maxRetries; attempt++) {
            try {
                const mapElement = document.getElementById('googlemaps');
                if (!mapElement) {
                    throw new Error('googlemaps elementi bulunamadı');
                }
                
                await loadGoogleMapsAPI();
                const map = await initializeMap();
                
                window.addEventListener('resize', () => {
                    setTimeout(() => {
                        google.maps.event.trigger(map, 'resize');
                        const centerLat = parseFloat(mapElement.getAttribute('data-center-lat')) || 41.0048046;
                        const centerLng = parseFloat(mapElement.getAttribute('data-center-lng')) || 39.7267915;
                        map.setCenter({ lat: centerLat, lng: centerLng });
                    }, 100);
                });
                
                return;
                
            } catch (error) {
                if (attempt === maxRetries) {
                    showMapError(error.message);
                } else {
                    await new Promise(resolve => setTimeout(resolve, 1000 * attempt));
                }
            }
        }
    }
    
    function showMapError(errorMessage) {
        const mapElement = document.getElementById('googlemaps');
        if (mapElement) {
            mapElement.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: center; 
                           height: 400px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); 
                           color: #555; font-family: 'Segoe UI', Arial, sans-serif; 
                           border: 1px solid #ddd; border-radius: 8px; text-align: center;">
                    <div>
                        <div style="font-size: 48px; margin-bottom: 16px; opacity: 0.7;">🗺️</div>
                        <div style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">
                            Harita Yüklenemedi
                        </div>
                        <div style="font-size: 14px; color: #777; margin-bottom: 16px;">
                            ${errorMessage}
                        </div>
                        <button onclick="location.reload()" 
                                style="background: #1a73e8; color: white; border: none; 
                                       padding: 10px 20px; border-radius: 6px; cursor: pointer; 
                                       font-size: 14px; font-weight: 500;">
                            Sayfayı Yenile
                        </button>
                    </div>
                </div>
            `;
        }
    }
    
    function initWithMultipleStrategies() {
        let isInitialized = false;
        
        function safeInit() {
            if (isInitialized) {
                return;
            }
            isInitialized = true;
            startGoogleMapsWithRetry();
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(safeInit, 100);
            });
        } else {
            setTimeout(safeInit, 100);
        }
        
        window.addEventListener('load', () => {
            setTimeout(() => {
                if (!isInitialized) {
                    const mapElement = document.getElementById('googlemaps');
                    if (mapElement && mapElement.children.length === 0) {
                        safeInit();
                    }
                }
            }, 500);
        });
        
        const mapElement = document.getElementById('googlemaps');
        if (mapElement) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && entry.target.children.length === 0 && !isInitialized) {
                        observer.disconnect();
                        setTimeout(safeInit, 200);
                    }
                });
            }, { threshold: 0.1 });
            
            observer.observe(mapElement);
        }
    }
    
    initWithMultipleStrategies();
    
})();