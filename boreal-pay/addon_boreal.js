// Menu Boreal Pay
$('.navbar-start').append(`
    <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link is-size-7 has-text-weight-bold"><i class="fa fa-qrcode is-hidden-desktop-only"></i>&nbsp; Boreal Pay</a>
        <div class="navbar-dropdown">
            <a href="/admin/clientes.hhvm?tipo=todos" class="navbar-item"><i class="fa fa-plus"></i>&nbsp; Criar faturas</a>
            <div class="dropdown-divider"></div>
            <a href="/admin/addons/boreal-pay/boreal/faturas.index.php" class="navbar-item"><i class="fa fa-search"></i>&nbsp; Faturas</a>
            <a href="/admin/addons/boreal-pay/boreal/configuracoes.php" class="navbar-item"><i class="fa fa-cog"></i>&nbsp; Configura\u00e7\u00f5es</a>
            <a href="/admin/addons/boreal-pay/boreal/instalar.php" class="navbar-item"><i class="fa fa-plug"></i>&nbsp; Instalar/Atualizar</a>
            <a href="/admin/addons/boreal-pay/boreal/logs.php" class="navbar-item"><i class="fa fa-info-circle"></i>&nbsp; Logs APIs</a>
        </div>
    </div>`);

function borealGetUrlVar(key) {
    var value = [];
    var query = String(document.location).split('?');
    if (query[1]) {
        var part = query[1].split('&');
        for (var i = 0; i < part.length; i++) {
            var data = part[i].split('=');
            if (data[0] && data[1]) {
                value[data[0]] = data[1];
            }
        }
        if (value[key]) {
            return value[key];
        }
    }
    return '';
}

function borealGerarPagamento(tituloId, tipo) {
    if (!tituloId) {
        alert('N\u00e3o foi poss\u00edvel identificar a fatura do MK Auth.');
        return;
    }
    var url = '/admin/addons/boreal-pay/boreal/visualizar.php?titulo=' + tituloId + '&tipo=' + (tipo || 'pix');
    window.open(url, '_blank');
}

// A\u00e7\u00f5es em detalhes do cliente
if (typeof window.location.pathname !== 'undefined') {
    if (window.location.pathname === '/admin/cliente_det.hhvm') {
        var boletoLink = document.querySelector('a[href*="prepara_boleto.hhvm?titulo="]');
        if (boletoLink) {
            var partes = boletoLink.href.split('?');
            var params = partes[1] || '';
            var tituloId = params.split('titulo=')[1];
            if (tituloId) {
                boletoLink.setAttribute('href', 'javascript:void(0);');
                boletoLink.setAttribute('onclick', "borealGerarPagamento('" + tituloId + "','boleto');");
            }
        }

        var pixButton = document.querySelector('a[title="Imprimir titulos"]');
        if (pixButton) {
            var tituloParam = borealGetUrlVar('titulo');
            if (tituloParam) {
                pixButton.setAttribute('href', 'javascript:void(0);');
                pixButton.setAttribute('onclick', "borealGerarPagamento('" + tituloParam + "','pix');");
            }
        }
    }
}
