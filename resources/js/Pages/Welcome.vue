<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { benefits, features, floatingBadges, navLinks, steps } from '@/Constants/welcome';

defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const carousel = ref(null);

const scrollFeatures = (direction) => {
    carousel.value?.scrollBy({ left: direction * 320, behavior: 'smooth' });
};
</script>

<template>
    <Head title="Bem-vindo" />

    <div class="relative overflow-hidden bg-base-100 text-base-content">
        <!-- Formas orgânicas de fundo -->
        <div class="pointer-events-none absolute -top-40 -right-40 size-[40rem] rounded-full bg-brand-50" />
        <div class="pointer-events-none absolute top-32 -left-48 size-96 rounded-full bg-brand-50/60" />

        <!-- Navbar -->
        <header class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6">
            <Link href="/" class="block w-36 sm:w-40">
                <img src="/images/logo.png" alt="Vistoria Solar" class="w-full" />
            </Link>

            <nav class="hidden items-center gap-8 text-sm font-medium text-ink-soft lg:flex">
                <a v-for="link in navLinks" :key="link.href" :href="link.href" class="transition hover:text-primary">
                    {{ link.label }}
                </a>
            </nav>

            <div v-if="canLogin" class="flex items-center gap-2">
                <Link
                    v-if="$page.props.auth?.user"
                    :href="route('dashboard')"
                    class="btn btn-primary btn-sm rounded-full px-5"
                >
                    Painel
                </Link>
                <template v-else>
                    <Link :href="route('login')" class="btn btn-ghost btn-sm rounded-full">Entrar</Link>
                    <Link v-if="canRegister" :href="route('register')" class="btn btn-primary btn-sm rounded-full px-5">
                        Começar
                    </Link>
                </template>
            </div>
        </header>

        <!-- Hero -->
        <section id="inicio" class="relative z-10 mx-auto grid max-w-7xl items-center gap-12 px-4 pt-8 pb-20 sm:px-6 lg:grid-cols-2 lg:pt-16 lg:pb-28">
            <div>
                <p class="text-xs font-semibold tracking-widest text-primary uppercase">Uma plataforma de vistoria solar</p>
                <h1 class="mt-4 text-4xl leading-tight font-bold text-ink sm:text-5xl">
                    Vistorias solares organizadas do início ao fim
                </h1>
                <p class="mt-6 max-w-lg text-lg text-ink-muted">
                    Gerencie clientes, equipamentos, vistorias e processos de homologação de sistemas fotovoltaicos em
                    um só lugar.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="btn btn-primary btn-lg rounded-full px-8 shadow-lg shadow-brand-700/20"
                    >
                        Ir para o painel
                        <Icon icon="mdi:arrow-right" class="size-5" />
                    </Link>
                    <template v-else-if="canLogin">
                        <Link
                            :href="route(canRegister ? 'register' : 'login')"
                            class="btn btn-primary btn-lg rounded-full px-8 shadow-lg shadow-brand-700/20"
                        >
                            Começar agora
                            <Icon icon="mdi:arrow-right" class="size-5" />
                        </Link>
                        <Link :href="route('login')" class="btn btn-lg btn-ghost rounded-full text-primary">
                            Já tenho conta
                        </Link>
                    </template>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-xl">
                <div class="absolute inset-8 rounded-full bg-brand-100/70 blur-3xl" />
                <img src="/images/notebook.png" alt="Plataforma Vistoria Solar" class="relative w-full drop-shadow-2xl" />

                <span
                    v-for="badge in floatingBadges"
                    :key="badge.icon"
                    class="absolute flex size-12 animate-float items-center justify-center rounded-2xl shadow-md"
                    :class="badge.class"
                    :style="{ animationDelay: badge.delay }"
                >
                    <Icon :icon="badge.icon" class="size-6" />
                </span>
            </div>
        </section>

        <!-- Como funciona -->
        <section id="como-funciona" class="relative z-10 mx-auto max-w-5xl px-4 py-20 text-center sm:px-6">
            <p class="text-xs font-semibold tracking-widest text-primary uppercase">Como funciona</p>
            <h2 class="mt-3 text-3xl font-bold text-ink sm:text-4xl">Simples como deve ser</h2>
            <p class="mx-auto mt-4 max-w-2xl text-ink-muted">
                Da primeira visita ao cliente até a aprovação na concessionária, cada etapa fica registrada na
                plataforma.
            </p>

            <div class="relative mt-16 grid gap-12 md:grid-cols-3">
                <!-- Linha que liga os passos -->
                <div class="absolute top-8 right-[16%] left-[16%] hidden border-t-2 border-dashed border-brand-200 md:block" />

                <div v-for="step in steps" :key="step.title" class="relative">
                    <span class="mx-auto flex size-16 items-center justify-center rounded-2xl border-2 border-brand-200 bg-base-100 text-primary">
                        <Icon :icon="step.icon" class="size-8" />
                    </span>
                    <h3 class="mt-5 text-lg font-semibold text-ink">{{ step.title }}</h3>
                    <p class="mx-auto mt-2 max-w-xs text-sm text-ink-muted">{{ step.text }}</p>
                </div>
            </div>
        </section>

        <!-- Recursos (carrossel) -->
        <section id="recursos" class="relative z-10 py-20">
            <div class="pointer-events-none absolute top-0 -left-40 size-[36rem] rounded-full bg-brand-50/70" />

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <p class="text-xs font-semibold tracking-widest text-primary uppercase">Saiba mais</p>
                        <h2 class="mt-3 text-3xl font-bold text-ink sm:text-4xl">Recursos</h2>
                        <p class="mt-4 max-w-xl text-ink-muted">
                            Tudo o que sua empresa de energia solar precisa para trabalhar com agilidade.
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" class="btn btn-circle btn-primary btn-sm" aria-label="Anterior" @click="scrollFeatures(-1)">
                            <Icon icon="mdi:chevron-left" class="size-5" />
                        </button>
                        <button type="button" class="btn btn-circle btn-primary btn-sm" aria-label="Próximo" @click="scrollFeatures(1)">
                            <Icon icon="mdi:chevron-right" class="size-5" />
                        </button>
                    </div>
                </div>

                <div
                    ref="carousel"
                    class="-mx-4 mt-10 flex snap-x snap-mandatory gap-6 overflow-x-auto px-4 pt-2 pb-8 [scrollbar-width:none] sm:-mx-6 sm:px-6"
                >
                    <article
                        v-for="feature in features"
                        :key="feature.title"
                        class="w-72 shrink-0 snap-start rounded-box bg-base-100 p-7 shadow-lg shadow-ink/5 transition hover:-translate-y-1 hover:shadow-xl"
                    >
                        <span class="flex size-14 items-center justify-center rounded-full" :class="feature.color">
                            <Icon :icon="feature.icon" class="size-7" />
                        </span>
                        <h3 class="mt-6 font-semibold text-ink">{{ feature.title }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-ink-muted">{{ feature.text }}</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Benefícios -->
        <section id="beneficios" class="relative z-10 mx-auto grid max-w-7xl items-center gap-16 px-4 py-20 sm:px-6 lg:grid-cols-2">
            <div class="relative mx-auto w-full max-w-md">
                <div class="aspect-square rounded-full bg-brand-50" />
                <img src="/images/vistoria.png" alt="Vistoria de painel solar" class="absolute inset-0 m-auto h-3/4 w-auto" />
            </div>

            <div>
                <p class="text-xs font-semibold tracking-widest text-primary uppercase">Por que usar</p>
                <h2 class="mt-3 text-3xl font-bold text-ink sm:text-4xl">Benefícios</h2>
                <p class="mt-4 max-w-lg text-ink-muted">
                    Menos retrabalho, mais controle. A Exata Engenharia criou o Vistoria Solar para quem vive o dia a
                    dia da energia fotovoltaica.
                </p>

                <ul class="mt-10 grid gap-6 sm:grid-cols-2">
                    <li v-for="benefit in benefits" :key="benefit" class="flex items-start gap-3">
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary text-primary-content">
                            <Icon icon="mdi:check" class="size-4" />
                        </span>
                        <span class="font-medium text-ink">{{ benefit }}</span>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Chamada final -->
        <section v-if="canLogin && !$page.props.auth?.user" class="relative z-10 mx-auto max-w-7xl px-4 pb-20 sm:px-6">
            <div class="rounded-box bg-brand-gradient bg-size-[400%_400%] animate-gradient px-8 py-14 text-center text-white">
                <h2 class="font-mono text-3xl drop-shadow-md">
                    Vistoria <span class="tracking-[8px] uppercase">Solar</span>
                </h2>
                <p class="mt-3 text-xl font-semibold drop-shadow-md">Nossa fonte de energia é o sol</p>
                <Link
                    :href="route(canRegister ? 'register' : 'login')"
                    class="btn btn-lg mt-8 rounded-full border-none bg-white px-8 text-brand-700 hover:bg-brand-50"
                >
                    Criar minha conta
                </Link>
            </div>
        </section>

        <footer class="relative z-10 border-t border-surface-line">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm text-ink-muted sm:flex-row sm:px-6">
                <img src="/images/logo.png" alt="Vistoria Solar" class="w-28" />
                <p class="text-center">&copy; {{ new Date().getFullYear() }} Exata Engenharia. Todos os direitos reservados.</p>
            </div>
        </footer>
    </div>
</template>
