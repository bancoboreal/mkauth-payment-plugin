/* ==========================================================
   INTEGRAÇÃO BOREAL PAY
   Injeta o menu e funções de pagamento automaticamente
   ========================================================== */

jQuery(document).ready(function($) {
    
    console.log("[Boreal] Carregando integração via addon.js...");

    // 1. INJEÇÃO DO MENU BOREAL
    if ($('#menu-boreal-pay').length === 0) {
        
        var menuHtml = `
            <div class="navbar-item has-dropdown is-hoverable" id="menu-boreal-pay">
                <a class="navbar-link is-size-7 has-text-weight-bold">
                    <i class="fa fa-qrcode is-hidden-desktop-only"></i>&nbsp; BOREAL PAY
                </a>
                <div class="navbar-dropdown">
                    <a href="/admin/clientes.hhvm?tipo=todos" class="navbar-item">
                        <i class="fa fa-plus"></i>&nbsp; Criar faturas
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="/admin/addons/boreal-pay/boreal/faturas.index.php" class="navbar-item">
                        <i class="fa fa-search"></i>&nbsp; Faturas
                    </a>
                    <a href="/admin/addons/boreal-pay/boreal/configuracoes.php" class="navbar-item">
                        <i class="fa fa-cog"></i>&nbsp; Configurações
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="/admin/addons/boreal-pay/boreal/instalar.php" class="navbar-item">
                        <i class="fa fa-plug"></i>&nbsp; Instalar/Reparar
                    </a>
                    <a href="/admin/addons/boreal-pay/boreal/logs.php" class="navbar-item">
                        <i class="fa fa-info-circle"></i>&nbsp; Logs
                    </a>
                </div>
            </div>`;

        // Tenta inserir logo após o menu OPÇÕES ou no final
        $('.navbar-start').append(menuHtml);
    }

    // 2. FUNÇÕES DE PAGAMENTO (Globais)
    window.borealGerarPagamento = function(tituloId, tipo) {
        if (!tituloId) {
            alert('Fatura não identificada.');
            return;
        }
        // Centraliza o popup
        var w = 450; var h = 650;
        var left = (screen.width/2)-(w/2);
        var top = (screen.height/2)-(h/2);
        var url = '/admin/addons/boreal-pay/boreal/visualizar.php?titulo=' + tituloId + '&tipo=' + (tipo || 'pix');
        
        window.open(url, 'BorealPay', 'width='+w+', height='+h+', top='+top+', left='+left+', scrollbars=yes, resizable=yes');
    };

    function borealGetUrlVar(key) {
        var query = String(window.location.search).substring(1);
        var vars = query.split('&');
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            if (decodeURIComponent(pair[0]) == key) {
                return decodeURIComponent(pair[1]);
            }
        }
        return '';
    }

    // 3. INTERCEPTAÇÃO NA TELA DO CLIENTE (Botões Verdes/Impressora)
    if (window.location.href.indexOf('cliente_det.hhvm') > -1) {
        
        // Substitui o link do boleto nativo (ícone de código de barras)
        $('a[href*="prepara_boleto.hhvm"]').each(function() {
            var href = $(this).attr('href');
            if (href && href.indexOf('titulo=') > -1) {
                var match = href.match(/titulo=(\d+)/);
                if(match && match[1]) {
                    $(this).attr('href', 'javascript:void(0);');
                    $(this).attr('onclick', "borealGerarPagamento('" + match[1] + "','boleto'); return false;");
                    $(this).css('color', '#00d1b2'); // Destaque verde
                }
            }
        });

        // Substitui o botão "Imprimir titulos" (para gerar Pix)
        var pixButton = $('a[title="Imprimir titulos"]');
        if (pixButton.length > 0) {
            var tituloParam = borealGetUrlVar('titulo');
            if (tituloParam) {
                pixButton.attr('href', 'javascript:void(0);');
                pixButton.attr('onclick', "borealGerarPagamento('" + tituloParam + "','pix'); return false;");
                // Tenta mudar o ícone para QR Code se existir
                pixButton.find('i').removeClass('bi-printer-fill').addClass('bi-qr-code');
            }
        }
    }
});