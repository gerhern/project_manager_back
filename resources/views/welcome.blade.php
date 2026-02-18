<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Project Manager API</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            const translations = {
                es: {
                    title: "API de Gestión de Proyectos",
                    desc: "Esta es una instancia de demostración activa del backend. Por favor, consulta la documentación oficial para interactuar con los endpoints.",
                    btn: "Ver Documentación en GitHub",
                    status: "Estado: En Línea"
                },
                en: {
                    title: "Project Manager API",
                    desc: "This is an active backend demonstration instance. Please refer to the official documentation to interact with the endpoints.",
                    btn: "View Documentation on GitHub",
                    status: "Status: Online"
                },
                de: {
                    title: "Projektmanager-API",
                    desc: "Dies ist eine aktive Backend-Demonstrationsinstanz. Bitte lesen Sie die offizielle Dokumentation, um mit den Endpunkten zu interagieren.",
                    btn: "Dokumentation auf GitHub ansehen",
                    status: "Status: Online"
                }
            };

            function setLanguage(lang) {
                localStorage.setItem('pref_lang', lang);
                document.getElementById('api-title').innerText = translations[lang].title;
                document.getElementById('api-desc').innerText = translations[lang].desc;
                document.getElementById('api-btn-text').innerText = translations[lang].btn;
                document.getElementById('api-status').innerText = translations[lang].status;
                
                // Actualizar estilo de botones
                document.querySelectorAll('.lang-btn').forEach(btn => {
                    btn.classList.remove('text-indigo-400', 'border-indigo-400');
                    if(btn.id === 'btn-' + lang) btn.classList.add('text-indigo-400', 'border-b-2', 'border-indigo-400');
                });
            }

            window.onload = () => {
                const userLang = localStorage.getItem('pref_lang') || navigator.language.slice(0, 2) || 'en';
                setLanguage(translations[userLang] ? userLang : 'en');
            };
        </script>
    </head>
    <body class="bg-slate-900 text-slate-200 flex items-center justify-center min-h-screen font-sans p-4">
        <div class="max-w-md w-full p-8 bg-slate-800 rounded-xl shadow-2xl border border-slate-700 text-center relative">
            
            <div class="absolute top-4 right-8 flex gap-3 text-xs font-bold uppercase tracking-tighter">
                <button id="btn-es" onclick="setLanguage('es')" class="lang-btn hover:text-white transition-colors">ES</button>
                <button id="btn-en" onclick="setLanguage('en')" class="lang-btn hover:text-white transition-colors">EN</button>
                <button id="btn-de" onclick="setLanguage('de')" class="lang-btn hover:text-white transition-colors">DE</button>
            </div>

            <div class="mb-6 mt-4 inline-flex p-4 bg-indigo-500/10 rounded-full text-indigo-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
            </div>

            <h1 id="api-title" class="text-2xl font-bold text-white mb-2 tracking-tight"></h1>
            <p id="api-desc" class="text-slate-400 mb-8 text-sm leading-relaxed"></p>
            
            <a href="https://github.com/gerhern/project_manager_back" target="_blank" class="flex items-center justify-center w-full px-6 py-3 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-500/20 gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.11.825-.26.825-.58 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                <span id="api-btn-text"></span>
            </a>
            
            <div class="mt-8 pt-6 border-t border-slate-700 flex justify-between items-center text-[10px] text-slate-500 uppercase tracking-[0.2em]">
                <span id="api-status"></span>
                <span class="flex h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)] animate-pulse"></span>
            </div>
        </div>
    </body>
    </html>