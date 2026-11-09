{{-- Incluído em todas as páginas do painel (AdminPanelProvider, BODY_END). --}}
<script>
    // Campos com data-casas-decimais (App\Filament\Support\CampoNumerico) não
    // aceitam mais casas decimais do que a coluna do banco guarda.
    // Os ouvintes ficam no document para valer também nos formulários em modal.
    document.addEventListener('keydown', (evento) => {
        const campo = evento.target;

        if (! campo.dataset || campo.dataset.casasDecimais === undefined) {
            return;
        }

        // "e" e "+" são aceitos por inputs numéricos (notação científica).
        const bloqueadas = campo.dataset.casasDecimais === '0' ? ['e', 'E', '+', '.', ','] : ['e', 'E', '+'];

        if (bloqueadas.includes(evento.key)) {
            evento.preventDefault();
        }
    });

    document.addEventListener('input', (evento) => {
        const campo = evento.target;

        if (! campo.dataset || campo.dataset.casasDecimais === undefined) {
            return;
        }

        const casas = Number(campo.dataset.casasDecimais);
        const excesso = casas === 0
            ? /^(-?\d*)\.\d*$/
            : new RegExp('^(-?\\d*\\.\\d{' + casas + '})\\d+$');

        if (! excesso.test(campo.value)) {
            return;
        }

        campo.value = campo.value.replace(excesso, '$1');

        // O Livewire leu o valor antes deste ouvinte: avisa de novo, já cortado.
        campo.dispatchEvent(new Event('input', { bubbles: true }));
    });

    // Mensagem do Livewire quando a sessão expira (vem em inglês).
    const traduzirExpiracao = () => Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status !== 419) {
                return;
            }

            preventDefault();
            confirm('Esta página expirou.\nDeseja recarregar a página?') && window.location.reload();
        });
    });

    window.Livewire ? traduzirExpiracao() : document.addEventListener('livewire:init', traduzirExpiracao);
</script>
