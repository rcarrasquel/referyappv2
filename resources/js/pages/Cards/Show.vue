<template>
    <Head title="Card Detail" />

    <div class="relative">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_right,_rgba(109,190,69,0.14),_transparent_45%),radial-gradient(circle_at_bottom_left,_rgba(17,17,17,0.08),_transparent_40%)]" />

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#6DBE45]">Card Studio</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">{{ state.name || 'Untitled card' }}</h1>
                <p class="mt-1 text-sm text-slate-500">@{{ state.username || 'username' }}</p>
            </div>

            <Link href="/cards">
                <Button variant="secondary" type="button">
                    <span class="inline-flex items-center gap-2">
                        <ArrowLeftIcon class="h-4 w-4" />
                        Back to Cards
                    </span>
                </Button>
            </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-12">
            <section class="space-y-4 lg:col-span-8 lg:min-w-0 lg:pr-1">
                <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                    <div class="studio-scroll flex gap-2 overflow-x-auto whitespace-nowrap pb-1">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            type="button"
                            class="shrink-0 rounded-xl px-3 py-2 text-sm font-semibold transition"
                            :class="activeTab === tab.key
                                ? 'bg-[#111111] text-white'
                                : 'text-slate-600 hover:bg-slate-100'"
                            @click="activeTab = tab.key"
                        >
                            <span class="inline-flex items-center gap-2">
                                <component :is="tab.icon" class="h-4 w-4" />
                                {{ tab.label }}
                            </span>
                        </button>
                    </div>
                </div>

                <Card v-if="activeTab === 'basic'">
                    <p class="mb-4 text-base font-semibold text-slate-900">Basic Information</p>
                    <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="saveBasicInfo">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Card Name</label>
                            <input v-model="state.name" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                            <p v-if="basicForm.errors.name" class="mt-1 text-xs text-rose-600">{{ basicForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Username</label>
                            <input v-model="state.username" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                            <p v-if="basicForm.errors.username" class="mt-1 text-xs text-rose-600">{{ basicForm.errors.username }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                            <input v-model="state.description" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                            <p v-if="basicForm.errors.description" class="mt-1 text-xs text-rose-600">{{ basicForm.errors.description }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Phone</label>
                            <input v-model="state.phone" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                            <p v-if="basicForm.errors.phone" class="mt-1 text-xs text-rose-600">{{ basicForm.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                            <input v-model="state.email" type="email" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                            <p v-if="basicForm.errors.email" class="mt-1 text-xs text-rose-600">{{ basicForm.errors.email }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Address</label>
                            <input v-model="state.address" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                            <p v-if="basicForm.errors.address" class="mt-1 text-xs text-rose-600">{{ basicForm.errors.address }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Google Maps URL</label>
                            <input v-model="state.googleMapsUrl" type="text" placeholder="https://maps.google.com/..." class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                            <p v-if="basicForm.errors.google_maps_url" class="mt-1 text-xs text-rose-600">{{ basicForm.errors.google_maps_url }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="mb-2 text-xs text-slate-500">{{ copy.usernameWarning }}</p>
                            <Button type="submit" :disabled="basicForm.processing">
                                <span class="inline-flex items-center gap-2">
                                    <CheckIcon class="h-4 w-4" />
                                    {{ copy.saveBasicInfo }}
                                </span>
                            </Button>
                        </div>
                    </form>
                </Card>

                <Card v-else-if="activeTab === 'colors'">
                    <p class="mb-4 text-base font-semibold text-slate-900">Colors</p>
                    <form class="space-y-4" @submit.prevent="saveColors">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Header</label>
                                <input v-model="state.headerColor" type="color" class="h-10 w-full rounded-lg border border-slate-200 p-1">
                                <p v-if="colorsForm.errors.header_color" class="mt-1 text-xs text-rose-600">{{ colorsForm.errors.header_color }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Text</label>
                                <input v-model="state.textColor" type="color" class="h-10 w-full rounded-lg border border-slate-200 p-1">
                                <p v-if="colorsForm.errors.text_color" class="mt-1 text-xs text-rose-600">{{ colorsForm.errors.text_color }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Button Bg</label>
                                <input v-model="state.buttonBackgroundColor" type="color" class="h-10 w-full rounded-lg border border-slate-200 p-1">
                                <p v-if="colorsForm.errors.button_background_color" class="mt-1 text-xs text-rose-600">{{ colorsForm.errors.button_background_color }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Button Text</label>
                                <input v-model="state.buttonTextColor" type="color" class="h-10 w-full rounded-lg border border-slate-200 p-1">
                                <p v-if="colorsForm.errors.button_text_color" class="mt-1 text-xs text-rose-600">{{ colorsForm.errors.button_text_color }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Background</label>
                                <input v-model="state.backgroundColor" type="color" class="h-10 w-full rounded-lg border border-slate-200 p-1">
                                <p v-if="colorsForm.errors.background_color" class="mt-1 text-xs text-rose-600">{{ colorsForm.errors.background_color }}</p>
                            </div>
                        </div>

                        <Button type="submit" :disabled="colorsForm.processing">
                            <span class="inline-flex items-center gap-2">
                                <CheckIcon class="h-4 w-4" />
                                Save Colors
                            </span>
                        </Button>
                    </form>
                </Card>

                <Card v-else-if="activeTab === 'images'">
                    <p class="mb-4 text-base font-semibold text-slate-900">Images</p>
                    <form class="space-y-4" @submit.prevent="saveImages">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <p class="text-sm font-medium text-slate-700">Profile Image</p>
                                <div class="flex flex-wrap items-start gap-4">
                                    <div class="space-y-2">
                                        <div class="relative h-[150px] w-[150px]">
                                            <button type="button" class="upload-mask h-[150px] w-[150px]" @click="triggerInput('profile')">
                                                <img v-if="state.profileImagePreview" :src="state.profileImagePreview" alt="Profile" class="h-full w-full object-cover" :class="profilePreviewClass" />
                                                <div v-else class="flex h-full w-full flex-col items-center justify-center gap-1 text-slate-500">
                                                    <PhotoIcon class="h-5 w-5" />
                                                    <span class="text-xs font-medium">Upload profile</span>
                                                </div>
                                            </button>
                                            <button
                                                v-if="state.profileImagePreview"
                                                type="button"
                                                class="absolute -right-2 -top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-600 text-white shadow-lg transition hover:scale-105 hover:bg-rose-700"
                                                :title="copy.removeProfileImage"
                                                @click="openRemoveProfileModal"
                                            >
                                                <TrashIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>

                                    <div class="min-w-[180px] space-y-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ copy.profileImageStyleTitle }}</p>
                                        <div class="space-y-2">
                                            <button
                                                v-for="option in profileImageStyles"
                                                :key="option.value"
                                                type="button"
                                                class="block w-full rounded-lg border px-3 py-2 text-left text-xs font-medium transition"
                                                :class="state.profileImageStyle === option.value ? 'border-[#6DBE45] bg-[#6DBE45]/15 text-[#111111]' : 'border-slate-200 text-slate-600'"
                                                @click="state.profileImageStyle = option.value"
                                            >
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <input ref="profileInputRef" type="file" accept="image/*" class="hidden" @change="onProfileSelected">
                                <p v-if="imagesForm.errors.profile_image" class="text-xs text-rose-600">{{ imagesForm.errors.profile_image }}</p>
                            </div>

                            <div class="grid gap-4 border-t border-slate-100 pt-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <p class="text-sm font-medium text-slate-700">Header Image</p>
                                    <div class="relative max-w-[320px]">
                                        <button type="button" class="upload-mask aspect-[3/1] w-full max-h-[110px]" @click="triggerInput('header')">
                                            <img v-if="state.headerImagePreview" :src="state.headerImagePreview" alt="Header" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full flex-col items-center justify-center gap-1 text-slate-500">
                                                <PhotoIcon class="h-5 w-5" />
                                                <span class="text-xs font-medium">Upload header</span>
                                            </div>
                                        </button>
                                        <button
                                            v-if="state.headerImagePreview"
                                            type="button"
                                            class="absolute -right-2 -top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-600 text-white shadow-lg transition hover:scale-105 hover:bg-rose-700"
                                            :title="copy.removeHeaderImage"
                                            @click="openRemoveHeaderModal"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-slate-500">{{ copy.headerImageHint }}</p>
                                    <input ref="headerInputRef" type="file" accept="image/*" class="hidden" @change="onHeaderSelected">
                                    <p v-if="imagesForm.errors.header_image" class="text-xs text-rose-600">{{ imagesForm.errors.header_image }}</p>
                                </div>

                                <div class="space-y-2">
                                    <p class="text-sm font-medium text-slate-700">Background Image</p>
                                    <div class="relative w-full max-w-[162px]">
                                        <button type="button" class="upload-mask aspect-[9/19.5] w-full max-h-[350px] rounded-2xl bg-slate-50" @click="triggerInput('background')">
                                            <img v-if="state.backgroundImagePreview" :src="state.backgroundImagePreview" alt="Background" class="h-full w-full object-contain" />
                                            <div v-else class="flex h-full w-full flex-col items-center justify-center gap-2 text-slate-500">
                                                <PhotoIcon class="h-6 w-6" />
                                                <span class="text-xs font-medium">Upload background</span>
                                                <span class="text-[11px] text-slate-400">Phone-style ratio preview</span>
                                            </div>
                                        </button>
                                        <button
                                            v-if="state.backgroundImagePreview"
                                            type="button"
                                            class="absolute -right-2 -top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-600 text-white shadow-lg transition hover:scale-105 hover:bg-rose-700"
                                            :title="copy.removeBackgroundImage"
                                            @click="openRemoveBackgroundModal"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <input ref="backgroundInputRef" type="file" accept="image/*" class="hidden" @change="onBackgroundSelected">
                                    <p v-if="imagesForm.errors.background_image" class="text-xs text-rose-600">{{ imagesForm.errors.background_image }}</p>
                                </div>
                            </div>
                        </div>

                        <Button type="submit" :disabled="imagesForm.processing">
                            <span class="inline-flex items-center gap-2">
                                <CheckIcon class="h-4 w-4" />
                                Save Images
                            </span>
                        </Button>
                    </form>
                </Card>

                <Card v-else-if="activeTab === 'links'">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <p class="text-base font-semibold text-slate-900">Links</p>
                        <Button type="button" variant="secondary" @click="addLink">
                            <span class="inline-flex items-center gap-2">
                                <PlusIcon class="h-4 w-4" />
                                {{ copy.addLink }}
                            </span>
                        </Button>
                    </div>

                    <p class="mb-4 text-xs text-slate-500">{{ copy.dragToSort }}</p>

                    <div v-if="state.links.length" class="space-y-3">
                        <div
                            v-for="(link, idx) in state.links"
                            :key="link._uid"
                            draggable="true"
                            class="rounded-xl border border-slate-200 p-3 transition"
                            :class="draggingLinkIndex === idx ? 'border-[#6DBE45] bg-[#6DBE45]/5' : ''"
                            @dragstart="onLinkDragStart(idx)"
                            @dragover.prevent
                            @drop="onLinkDrop(idx)"
                            @dragend="clearLinkDragState"
                        >
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="flex min-w-0 flex-1 items-center gap-3 rounded-lg px-1 py-1.5 text-left transition hover:bg-slate-50"
                                    @click="toggleLinkDetails(link._uid)"
                                >
                                    <div class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white">
                                        <component
                                            :is="getLinkIconOption(link.icon).icon"
                                            v-if="getLinkIconOption(link.icon).type === 'hero'"
                                            class="h-4 w-4 text-slate-600"
                                        />
                                        <svg
                                            v-else
                                            viewBox="0 0 24 24"
                                            class="h-4 w-4"
                                            :style="{ color: `#${getLinkIconOption(link.icon).icon.hex}` }"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path :d="getLinkIconOption(link.icon).icon.path" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-800">{{ link.title || copy.untitledLink }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ link.description || link.url || copy.noDetailsYet }}</p>
                                    </div>
                                    <ChevronDownIcon class="h-4 w-4 shrink-0 text-slate-400 transition" :class="openLinkUid === link._uid ? 'rotate-180' : ''" />
                                </button>

                                <div class="inline-flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-medium text-slate-500">
                                        <Bars3Icon class="h-3.5 w-3.5" />
                                        {{ copy.dragToSort }}
                                    </span>
                                    <Button type="button" variant="secondary" @click="removeLink(idx)">
                                        <span class="inline-flex items-center gap-2">
                                            <TrashIcon class="h-4 w-4" />
                                            {{ copy.remove }}
                                        </span>
                                    </Button>
                                </div>
                            </div>

                            <div v-if="openLinkUid === link._uid" class="mt-3 grid gap-3 border-t border-slate-100 pt-3 sm:grid-cols-[220px,1fr]">
                                <div class="space-y-2">
                                    <label class="block text-xs font-medium text-slate-600">{{ copy.linkIcon }}</label>
                                    <div class="flex items-center gap-2">
                                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white">
                                            <component
                                                :is="getLinkIconOption(link.icon).icon"
                                                v-if="getLinkIconOption(link.icon).type === 'hero'"
                                                class="h-5 w-5 text-slate-600"
                                            />
                                            <svg
                                                v-else
                                                viewBox="0 0 24 24"
                                                class="h-5 w-5"
                                                :style="{ color: `#${getLinkIconOption(link.icon).icon.hex}` }"
                                                fill="currentColor"
                                                aria-hidden="true"
                                            >
                                                <path :d="getLinkIconOption(link.icon).icon.path" />
                                            </svg>
                                        </div>
                                        <select v-model="link.icon" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                            <option
                                                v-for="option in linkIconOptions"
                                                :key="option.value"
                                                :value="option.value"
                                            >
                                                {{ option.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label class="mb-1 block text-xs font-medium text-slate-600">{{ copy.linkTitle }}</label>
                                        <input v-model="link.title" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="mb-1 block text-xs font-medium text-slate-600">{{ copy.linkUrl }}</label>
                                        <input v-model="link.url" type="text" placeholder="https://..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="mb-1 block text-xs font-medium text-slate-600">{{ copy.linkDescription }}</label>
                                        <input v-model="link.description" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                        {{ copy.noLinksYet }}
                    </div>

                    <div class="mt-4">
                        <Button type="button" :disabled="linksForm.processing" @click="saveLinks">
                            <span class="inline-flex items-center gap-2">
                                <CheckIcon class="h-4 w-4" />
                                {{ copy.saveLinks }}
                            </span>
                        </Button>
                        <p v-if="linksForm.errors.links" class="mt-2 text-xs text-rose-600">{{ linksForm.errors.links }}</p>
                    </div>
                </Card>

                <Card v-else-if="activeTab === 'schedule'">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <p class="text-base font-semibold text-slate-900">Schedule</p>
                        <Button type="button" variant="secondary" @click="addSchedule">
                            <span class="inline-flex items-center gap-2">
                                <PlusIcon class="h-4 w-4" />
                                Add Schedule
                            </span>
                        </Button>
                    </div>

                    <div v-if="state.schedule.length" class="space-y-3">
                        <div
                            v-for="(item, idx) in state.schedule"
                            :key="idx"
                            class="rounded-xl border border-slate-200 p-4"
                        >
                            <div class="flex items-end gap-3">
                                <div class="flex-1">
                                    <label class="mb-1 block text-xs font-medium text-slate-600">Day</label>
                                    <select v-model.number="item.day" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                        <option :value="0">Sunday</option>
                                        <option :value="1">Monday</option>
                                        <option :value="2">Tuesday</option>
                                        <option :value="3">Wednesday</option>
                                        <option :value="4">Thursday</option>
                                        <option :value="5">Friday</option>
                                        <option :value="6">Saturday</option>
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <label class="mb-1 block text-xs font-medium text-slate-600">Open</label>
                                    <input v-model="item.open" type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="flex-1">
                                    <label class="mb-1 block text-xs font-medium text-slate-600">Close</label>
                                    <input v-model="item.close" type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="pb-0.5">
                                    <Button type="button" variant="secondary" size="sm" @click="removeSchedule(idx)">
                                        <TrashIcon class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                        No schedule yet. Add your first schedule.
                    </div>

                    <div class="mt-4">
                        <Button type="button" :disabled="scheduleForm.processing" @click="saveSchedule">
                            <span class="inline-flex items-center gap-2">
                                <CheckIcon class="h-4 w-4" />
                                Save Schedule
                            </span>
                        </Button>
                        <p v-if="scheduleForm.errors.schedule" class="mt-2 text-xs text-rose-600">{{ scheduleForm.errors.schedule }}</p>
                    </div>
                </Card>

                <Card v-else-if="activeTab === 'design'">
                    <p class="mb-4 text-base font-semibold text-slate-900">Design</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <button
                            v-for="option in buttonStyles"
                            :key="option.value"
                            type="button"
                            class="rounded-xl border px-3 py-2 text-sm font-medium transition"
                            :class="state.buttonStyle === option.value
                                ? 'border-[#6DBE45] bg-[#6DBE45]/15 text-[#111111]'
                                : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                            @click="state.buttonStyle = option.value"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                    <div class="mt-4">
                        <Button type="button" :disabled="buttonStyleForm.processing" @click="saveButtonStyle">
                            <span class="inline-flex items-center gap-2">
                                <CheckIcon class="h-4 w-4" />
                                {{ copy.saveButtonStyle }}
                            </span>
                        </Button>
                        <p v-if="buttonStyleForm.errors.button_style" class="mt-2 text-xs text-rose-600">{{ buttonStyleForm.errors.button_style }}</p>
                    </div>
                </Card>

                <Card v-if="activeTab === 'styles'">
                    <p class="mb-4 text-base font-semibold text-slate-900">Styles</p>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        <button
                            v-for="style in styleTemplates"
                            :key="style.value"
                            type="button"
                            class="rounded-xl border px-3 py-3 text-left transition"
                            :class="state.templateStyle === style.value
                                ? 'border-[#6DBE45] bg-[#6DBE45]/10 shadow-sm'
                                : 'border-slate-200 bg-slate-50 hover:bg-slate-100'"
                            @click="state.templateStyle = style.value"
                        >
                            <p class="text-sm font-semibold text-slate-800">{{ style.name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ style.description }}</p>
                        </button>
                    </div>
                    <div class="mt-4">
                        <Button type="button" :disabled="stylesForm.processing" @click="saveStyleTemplate">
                            <span class="inline-flex items-center gap-2">
                                <CheckIcon class="h-4 w-4" />
                                {{ copy.saveStyle }}
                            </span>
                        </Button>
                        <p v-if="stylesForm.errors.template_style" class="mt-2 text-xs text-rose-600">{{ stylesForm.errors.template_style }}</p>
                    </div>
                </Card>
            </section>

            <section class="lg:col-span-4 lg:sticky lg:top-24 lg:self-start">
                <div class="mx-auto w-[320px] rounded-[2.7rem] bg-[#111111] p-3 shadow-[0_24px_60px_rgba(0,0,0,0.32)]">
                    <div class="mx-auto mb-2 h-6 w-32 rounded-full bg-black/60" />
                    <div class="h-[620px] rounded-[2rem] p-5" :style="phoneBackgroundStyle">
                        <div class="flex h-full flex-col overflow-hidden rounded-2xl shadow-[0_18px_40px_rgba(15,23,42,0.22)] backdrop-blur" :style="previewCardStyle">
                            <div class="z-0 w-full" :class="[headerShapeClass, headerHeightClass]" :style="headerStyle">
                                <svg
                                    v-if="showWaveDecoration"
                                    viewBox="0 0 320 80"
                                    preserveAspectRatio="none"
                                    class="absolute inset-x-0 bottom-0 h-10 w-full"
                                    :style="waveDecorationStyle"
                                    fill="currentColor"
                                >
                                    <path d="M0,32 C50,78 120,8 190,32 C250,52 285,48 320,36 L320,80 L0,80 Z" />
                                </svg>
                            </div>
                            <div class="relative z-20" :class="previewBodyContainerClass" :style="previewBodyStyle">
                                <div :class="profileWrapClass">
                                    <img
                                        v-if="state.profileImagePreview"
                                        :src="state.profileImagePreview"
                                        alt="Profile"
                                        :class="[profileImageClass, profilePreviewClass]"
                                    >
                                    <div v-else :class="[profileImageClass, profilePreviewClass, 'bg-slate-200']" />
                                </div>

                                <div :class="textBlockClass">
                                    <p class="text-lg font-semibold leading-tight" :class="textAlignClass" :style="{ color: state.textColor }">
                                        {{ state.name || 'Your Name' }}
                                    </p>
                                    <p class="text-xs" :class="textAlignClass" :style="{ color: state.textColor }">@{{ state.username || 'username' }}</p>
                                    <p class="mt-2 text-xs leading-relaxed" :class="textAlignClass" :style="{ color: state.textColor }">
                                        {{ state.description || 'Your short professional description appears here.' }}
                                    </p>
                                </div>

                                <div class="mt-4 space-y-2">
                                    <button
                                        v-for="(link, idx) in previewLinks"
                                        :key="idx"
                                        type="button"
                                        class="w-full px-3 py-2 text-sm font-semibold transition"
                                        :class="buttonShapeClass"
                                        :style="{ backgroundColor: state.buttonBackgroundColor, color: state.buttonTextColor }"
                                    >
                                        <span class="flex w-full items-center justify-center gap-2 text-left">
                                            <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center">
                                                <component
                                                    :is="getLinkIconOption(link.icon).icon"
                                                    v-if="getLinkIconOption(link.icon).type === 'hero'"
                                                    class="h-4 w-4"
                                                />
                                                <svg v-else viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
                                                    <path :d="getLinkIconOption(link.icon).icon.path" />
                                                </svg>
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate leading-tight">{{ link.title || 'New Link' }}</span>
                                                <span v-if="link.description" class="mt-0.5 block truncate text-[10px] font-normal leading-tight opacity-80">{{ link.description }}</span>
                                            </span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mx-auto mt-4 w-[320px] rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ copy.publicCardUrl }}</p>
                    <div class="mt-2 flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <p class="min-w-0 flex-1 truncate text-sm font-medium text-slate-700" :title="publicCardUrl">
                            {{ publicCardUrl }}
                        </p>
                        <button
                            type="button"
                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-[#6DBE45] hover:text-[#111111]"
                            :title="copy.copyUrl"
                            @click="copyPublicCardUrl"
                        >
                            <ClipboardDocumentIcon class="h-4 w-4" />
                        </button>
                    </div>
                    <p v-if="copiedPublicUrl" class="mt-1 text-xs font-medium text-[#6DBE45]">{{ copy.urlCopied }}</p>
                    <a
                        :href="publicCardUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#6DBE45] px-4 py-2.5 text-sm font-semibold text-[#111111] transition hover:bg-[#5da939]"
                    >
                        <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                        {{ copy.viewRealTime }}
                    </a>
                </div>
            </section>
        </div>
    </div>

    <Modal :show="showCropModal" :title="cropModalTitle" @close="closeCropModal">
        <div class="space-y-4">
            <div class="mx-auto overflow-hidden rounded-2xl border border-slate-200 bg-slate-100" :class="cropPreviewClass">
                <img
                    v-if="cropSource"
                    :src="cropSource"
                    alt="Crop"
                    class="h-full w-full object-cover"
                    :style="{ transform: `scale(${cropZoom}) translate(${cropOffsetX}%, ${cropOffsetY}%)` }"
                >
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Zoom</label>
                <input v-model.number="cropZoom" type="range" min="1" max="3" step="0.05" class="w-full">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Horizontal</label>
                    <input v-model.number="cropOffsetX" type="range" min="-100" max="100" step="1" class="w-full">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Vertical</label>
                    <input v-model.number="cropOffsetY" type="range" min="-100" max="100" step="1" class="w-full">
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button type="button" variant="secondary" @click="closeCropModal">
                    <span class="inline-flex items-center gap-2">
                        <XMarkIcon class="h-4 w-4" />
                        Cancel
                    </span>
                </Button>
                <Button type="button" @click="applyCrop">
                    <span class="inline-flex items-center gap-2">
                        <CheckIcon class="h-4 w-4" />
                        {{ copy.applyCrop }}
                    </span>
                </Button>
            </div>
        </div>
    </Modal>

    <Modal :show="showRemoveProfileModal" :title="copy.removeProfileImage" @close="closeRemoveProfileModal">
        <div class="space-y-5">
            <p class="text-sm leading-relaxed text-slate-600">
                {{ copy.removeProfileImageConfirm }}
            </p>
            <div class="flex justify-end gap-2">
                <Button type="button" variant="secondary" @click="closeRemoveProfileModal">
                    <span class="inline-flex items-center gap-2">
                        <XMarkIcon class="h-4 w-4" />
                        {{ copy.cancel }}
                    </span>
                </Button>
                <Button type="button" class="!bg-rose-600 hover:!bg-rose-700 focus:!ring-rose-300" @click="confirmRemoveProfileImage">
                    <span class="inline-flex items-center gap-2">
                        <TrashIcon class="h-4 w-4" />
                        {{ copy.confirmDelete }}
                    </span>
                </Button>
            </div>
        </div>
    </Modal>

    <Modal :show="showRemoveHeaderModal" :title="copy.removeHeaderImage" @close="closeRemoveHeaderModal">
        <div class="space-y-5">
            <p class="text-sm leading-relaxed text-slate-600">
                {{ copy.removeHeaderImageConfirm }}
            </p>
            <div class="flex justify-end gap-2">
                <Button type="button" variant="secondary" @click="closeRemoveHeaderModal">
                    <span class="inline-flex items-center gap-2">
                        <XMarkIcon class="h-4 w-4" />
                        {{ copy.cancel }}
                    </span>
                </Button>
                <Button type="button" class="!bg-rose-600 hover:!bg-rose-700 focus:!ring-rose-300" @click="confirmRemoveHeaderImage">
                    <span class="inline-flex items-center gap-2">
                        <TrashIcon class="h-4 w-4" />
                        {{ copy.confirmDelete }}
                    </span>
                </Button>
            </div>
        </div>
    </Modal>

    <Modal :show="showRemoveBackgroundModal" :title="copy.removeBackgroundImage" @close="closeRemoveBackgroundModal">
        <div class="space-y-5">
            <p class="text-sm leading-relaxed text-slate-600">
                {{ copy.removeBackgroundImageConfirm }}
            </p>
            <div class="flex justify-end gap-2">
                <Button type="button" variant="secondary" @click="closeRemoveBackgroundModal">
                    <span class="inline-flex items-center gap-2">
                        <XMarkIcon class="h-4 w-4" />
                        {{ copy.cancel }}
                    </span>
                </Button>
                <Button type="button" class="!bg-rose-600 hover:!bg-rose-700 focus:!ring-rose-300" @click="confirmRemoveBackgroundImage">
                    <span class="inline-flex items-center gap-2">
                        <TrashIcon class="h-4 w-4" />
                        {{ copy.confirmDelete }}
                    </span>
                </Button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Modal from '@/components/ui/Modal.vue';
import {
    ArrowTopRightOnSquareIcon,
    ArrowLeftIcon,
    Bars3Icon,
    ChevronDownIcon,
    ChatBubbleLeftEllipsisIcon,
    CheckIcon,
    ClipboardDocumentIcon,
    ClockIcon,
    EnvelopeIcon,
    IdentificationIcon,
    LinkIcon,
    MapPinIcon,
    PhoneIcon,
    PhotoIcon,
    PlusIcon,
    SparklesIcon,
    SwatchIcon,
    TrashIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { useLocale } from '@/composables/useLocale';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    siDiscord,
    siFacebook,
    siGithub,
    siGmail,
    siGoogle,
    siGooglemessages,
    siInstagram,
    siMessenger,
    siSnapchat,
    siSpotify,
    siStripe,
    siTelegram,
    siTiktok,
    siTwitch,
    siWhatsapp,
    siX,
    siYoutube,
} from 'simple-icons';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    card: {
        type: Object,
        required: true,
    },
});

const { t } = useLocale();

const toStorageUrl = (path) => (path ? `/storage/${path}` : '');

const tabs = [
    { key: 'basic', label: 'Basic', icon: IdentificationIcon },
    { key: 'colors', label: 'Colors', icon: SwatchIcon },
    { key: 'images', label: 'Images', icon: PhotoIcon },
    { key: 'links', label: 'Links', icon: LinkIcon },
    { key: 'schedule', label: 'Schedule', icon: ClockIcon },
    { key: 'design', label: 'Design', icon: SparklesIcon },
    { key: 'styles', label: 'Styles', icon: SparklesIcon },
];

const validTabs = tabs.map((tab) => tab.key);
const initialTab = (() => {
    if (typeof window === 'undefined') {
        return 'basic';
    }

    const queryTab = new URLSearchParams(window.location.search).get('tab');
    return queryTab && validTabs.includes(queryTab) ? queryTab : 'basic';
})();

const activeTab = ref(initialTab);
const buildLinkUid = () => {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    return `link-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
};

const createLinkItem = (item = {}) => ({
    _uid: item._uid ?? buildLinkUid(),
    icon: item.icon ?? 'link',
    title: item.title ?? item.label ?? '',
    url: item.url ?? '',
    description: item.description ?? '',
    auto_key: item.auto_key ?? '',
});

const state = reactive({
    name: props.card.name ?? '',
    username: props.card.username ?? '',
    description: props.card.description ?? '',
    phone: props.card.phone ?? '',
    email: props.card.email ?? '',
    address: props.card.address ?? '',
    googleMapsUrl: props.card.google_maps_url ?? '',
    headerColor: props.card.header_color ?? '#6DBE45',
    textColor: props.card.text_color ?? '#111111',
    buttonBackgroundColor: props.card.button_background_color ?? '#6DBE45',
    buttonTextColor: props.card.button_text_color ?? '#FFFFFF',
    backgroundColor: props.card.background_color ?? '#F5F5F5',
    buttonStyle: props.card.button_style ?? 'rounded',
    templateStyle: props.card.template_style ?? 'classic',
    profileImageStyle: props.card.profile_image_style ?? 'circle',
    profileImagePreview: toStorageUrl(props.card.profile_image),
    headerImagePreview: toStorageUrl(props.card.header_image),
    backgroundImagePreview: toStorageUrl(props.card.background_image),
    links: Array.isArray(props.card.links) && props.card.links.length
        ? props.card.links.map((item) => createLinkItem(item))
        : [],
    schedule: Array.isArray(props.card.schedule) && props.card.schedule.length
        ? props.card.schedule
        : [],
});

const profileImageFile = ref(null);
const headerImageFile = ref(null);
const backgroundImageFile = ref(null);

const basicForm = useForm({
    name: state.name,
    username: state.username,
    description: state.description,
    phone: state.phone,
    email: state.email,
    address: state.address,
    google_maps_url: state.googleMapsUrl,
});

const colorsForm = useForm({
    header_color: state.headerColor,
    text_color: state.textColor,
    button_background_color: state.buttonBackgroundColor,
    button_text_color: state.buttonTextColor,
    background_color: state.backgroundColor,
});

const stylesForm = useForm({
    template_style: state.templateStyle,
});

const buttonStyleForm = useForm({
    button_style: state.buttonStyle,
});

const linksForm = useForm({
    links: state.links,
});

const scheduleForm = useForm({
    schedule: state.schedule,
});

const imagesForm = useForm({
    profile_image_style: state.profileImageStyle,
    remove_profile_image: false,
    remove_header_image: false,
    remove_background_image: false,
    profile_image: null,
    header_image: null,
    background_image: null,
});

const profileInputRef = ref(null);
const headerInputRef = ref(null);
const backgroundInputRef = ref(null);

const showCropModal = ref(false);
const showRemoveProfileModal = ref(false);
const showRemoveHeaderModal = ref(false);
const showRemoveBackgroundModal = ref(false);
const copiedPublicUrl = ref(false);
const cropSource = ref('');
const originalImageFile = ref(null);
const cropTarget = ref('profile');
const cropZoom = ref(1);
const cropOffsetX = ref(0);
const cropOffsetY = ref(0);
const draggingLinkIndex = ref(null);
const openLinkUid = ref(state.links[0]?._uid ?? null);

const copy = computed(() =>
    t({
        en: {
            usernameWarning: 'If you have already shared your card, we recommend not changing this username.',
            saveBasicInfo: 'Save Basic Info',
            profileImageStyleTitle: 'Profile image style',
            removeProfileImage: 'Remove image',
            removeProfileImageConfirm: 'This action cannot be restored. Do you want to delete the current profile image?',
            removeHeaderImage: 'Remove header image',
            removeHeaderImageConfirm: 'This action cannot be restored. Do you want to delete the current header image?',
            headerImageHint: 'Recommended size: 1500 x 500 (3:1).',
            removeBackgroundImage: 'Remove background image',
            removeBackgroundImageConfirm: 'This action cannot be restored. Do you want to delete the current background image?',
            cancel: 'Cancel',
            confirmDelete: 'Delete image',
            cropProfileImageTitle: 'Crop Profile Image',
            cropHeaderImageTitle: 'Crop Header Image',
            applyCrop: 'Apply Crop',
            addLink: 'Add Link',
            saveLinks: 'Save Links',
            linkTitle: 'Link title',
            linkUrl: 'Link URL',
            linkDescription: 'Short description',
            linkIcon: 'Icon',
            noLinksYet: 'No links yet. Add your first link.',
            untitledLink: 'Untitled link',
            noDetailsYet: 'No details yet',
            remove: 'Remove',
            dragToSort: 'Drag to reorder',
            styleTemplateTitle: 'Card templates',
            saveStyle: 'Save Style',
            saveButtonStyle: 'Save Button Style',
            publicCardUrl: 'Public card URL',
            copyUrl: 'Copy URL',
            urlCopied: 'URL copied',
            viewRealTime: 'View My Card in Real Time',
        },
        es: {
            usernameWarning: 'Si ya has compartido tu tarjeta recomendamos no cambiar ese usuario.',
            saveBasicInfo: 'Guardar informacion basica',
            profileImageStyleTitle: 'Estilo de imagen de perfil',
            removeProfileImage: 'Eliminar imagen',
            removeProfileImageConfirm: 'Esta accion no podra ser restaurada. Deseas eliminar la imagen de perfil actual?',
            removeHeaderImage: 'Eliminar imagen de header',
            removeHeaderImageConfirm: 'Esta accion no podra ser restaurada. Deseas eliminar la imagen de header actual?',
            headerImageHint: 'Tamano recomendado: 1500 x 500 (3:1).',
            removeBackgroundImage: 'Eliminar imagen de fondo',
            removeBackgroundImageConfirm: 'Esta accion no podra ser restaurada. Deseas eliminar la imagen de fondo actual?',
            cancel: 'Cancelar',
            confirmDelete: 'Eliminar imagen',
            cropProfileImageTitle: 'Recortar imagen de perfil',
            cropHeaderImageTitle: 'Recortar imagen de header',
            applyCrop: 'Aplicar recorte',
            addLink: 'Agregar enlace',
            saveLinks: 'Guardar enlaces',
            linkTitle: 'Titulo del enlace',
            linkUrl: 'URL del enlace',
            linkDescription: 'Descripcion corta',
            linkIcon: 'Icono',
            noLinksYet: 'No hay enlaces todavia. Agrega tu primer enlace.',
            untitledLink: 'Enlace sin titulo',
            noDetailsYet: 'Sin detalles',
            remove: 'Eliminar',
            dragToSort: 'Arrastra para ordenar',
            styleTemplateTitle: 'Plantillas de tarjeta',
            saveStyle: 'Guardar estilo',
            saveButtonStyle: 'Guardar estilo de botones',
            publicCardUrl: 'URL publica de la tarjeta',
            copyUrl: 'Copiar URL',
            urlCopied: 'URL copiada',
            viewRealTime: 'Ver mi tarjeta en tiempo real',
        },
    })
);

const profileImageStyles = [
    { label: 'Circle', value: 'circle' },
    { label: 'Rounded', value: 'rounded' },
    { label: 'Square', value: 'square' },
];

const linkIconOptions = [
    { value: 'facebook', label: 'Facebook', type: 'simple', icon: siFacebook },
    { value: 'instagram', label: 'Instagram', type: 'simple', icon: siInstagram },
    { value: 'tiktok', label: 'TikTok', type: 'simple', icon: siTiktok },
    { value: 'youtube', label: 'YouTube', type: 'simple', icon: siYoutube },
    { value: 'x', label: 'X', type: 'simple', icon: siX },
    { value: 'spotify', label: 'Spotify', type: 'simple', icon: siSpotify },
    { value: 'google', label: 'Google', type: 'simple', icon: siGoogle },
    { value: 'gmail', label: 'Email', type: 'simple', icon: siGmail },
    { value: 'whatsapp', label: 'WhatsApp', type: 'simple', icon: siWhatsapp },
    { value: 'telegram', label: 'Telegram', type: 'simple', icon: siTelegram },
    { value: 'discord', label: 'Discord', type: 'simple', icon: siDiscord },
    { value: 'messenger', label: 'Messenger', type: 'simple', icon: siMessenger },
    { value: 'snapchat', label: 'Snapchat', type: 'simple', icon: siSnapchat },
    { value: 'twitch', label: 'Twitch', type: 'simple', icon: siTwitch },
    { value: 'github', label: 'GitHub', type: 'simple', icon: siGithub },
    { value: 'stripe', label: 'Stripe', type: 'simple', icon: siStripe },
    { value: 'googlemessages', label: 'Google Messages', type: 'simple', icon: siGooglemessages },
    { value: 'link', label: 'Link', type: 'hero', icon: LinkIcon },
    { value: 'location', label: 'Location / Address', type: 'hero', icon: MapPinIcon },
    { value: 'phone', label: 'Phone', type: 'hero', icon: PhoneIcon },
    { value: 'sms', label: 'SMS', type: 'hero', icon: ChatBubbleLeftEllipsisIcon },
    { value: 'email', label: 'Email (Generic)', type: 'hero', icon: EnvelopeIcon },
];

const linkIconMap = Object.fromEntries(linkIconOptions.map((option) => [option.value, option]));
const defaultLinkIcon = linkIconMap.link;
const publicCardUrl = computed(() => {
    if (typeof window === 'undefined') {
        return `/${state.username || props.card.username || ''}`;
    }

    const username = (state.username || props.card.username || '').toString().trim();
    return username ? `${window.location.origin}/${username}` : window.location.origin;
});

const previewLinks = computed(() => state.links
    .filter((link) => (link?.title ?? '').trim() || (link?.url ?? '').trim())
    .slice(0, 5));

const phoneBackgroundStyle = computed(() => ({
    background: state.backgroundColor,
}));

const previewCardStyle = computed(() => {
    if (!state.backgroundImagePreview) {
        return { backgroundColor: 'rgba(255,255,255,0.85)' };
    }

    return {
        backgroundImage: `url(${state.backgroundImagePreview})`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
    };
});

const headerStyle = computed(() => {
    if (state.headerImagePreview) {
        return {
            backgroundImage: `url(${state.headerImagePreview})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
        };
    }

    return { backgroundColor: state.headerColor };
});

const previewBodyStyle = computed(() => ({}));

const profilePreviewClass = computed(() => {
    if (isWaveTemplate.value) {
        return 'rounded-full';
    }

    if (state.profileImageStyle === 'square') {
        return 'rounded-none';
    }

    if (state.profileImageStyle === 'rounded') {
        return 'rounded-xl';
    }

    return 'rounded-full';
});

const buttonShapeClass = computed(() => {
    if (state.buttonStyle === 'square') {
        return 'rounded-none';
    }

    if (state.buttonStyle === 'normal') {
        return 'rounded-md';
    }

    return 'rounded-xl';
});

const buttonStyles = [
    { label: 'Rounded', value: 'rounded' },
    { label: 'Normal', value: 'normal' },
    { label: 'Square', value: 'square' },
];

const styleTemplates = [
    {
        value: 'classic',
        name: 'Classic Clean',
        description: 'Balanced layout with centered profile and clean header.',
        profilePlacement: 'center',
        headerVariant: 'clean',
        headerHeight: 'normal',
        contentDensity: 'normal',
    },
    {
        value: 'classic_left',
        name: 'Classic Left',
        description: 'Classic clean style with profile aligned left.',
        profilePlacement: 'left',
        headerVariant: 'clean',
        headerHeight: 'normal',
        contentDensity: 'normal',
    },
    {
        value: 'classic_right',
        name: 'Classic Right',
        description: 'Classic clean style with profile aligned right.',
        profilePlacement: 'right',
        headerVariant: 'clean',
        headerHeight: 'normal',
        contentDensity: 'normal',
    },
    {
        value: 'wave_left',
        name: 'Wave Left',
        description: 'Wavy header with profile aligned to the left.',
        profilePlacement: 'left',
        headerVariant: 'wave',
        headerHeight: 'normal',
        contentDensity: 'normal',
    },
    {
        value: 'wave_right',
        name: 'Wave Right',
        description: 'Wavy header with profile aligned to the right.',
        profilePlacement: 'right',
        headerVariant: 'wave',
        headerHeight: 'normal',
        contentDensity: 'normal',
    },
    {
        value: 'split_modern',
        name: 'Split Modern',
        description: 'Diagonal header accent and left compact info block.',
        profilePlacement: 'left',
        headerVariant: 'diagonal',
        headerHeight: 'normal',
        contentDensity: 'compact',
    },
    {
        value: 'soft_stack',
        name: 'Soft Stack',
        description: 'Layered soft wave with centered profile and cozy spacing.',
        profilePlacement: 'center',
        headerVariant: 'layered',
        headerHeight: 'normal',
        contentDensity: 'spacious',
    },
    {
        value: 'wave_center',
        name: 'Wave Center',
        description: 'Centered profile with a bold wavy header.',
        profilePlacement: 'center',
        headerVariant: 'wave',
        headerHeight: 'tall',
        contentDensity: 'normal',
    },
    {
        value: 'split_right',
        name: 'Split Right',
        description: 'Diagonal modern header with profile on the right.',
        profilePlacement: 'right',
        headerVariant: 'diagonal',
        headerHeight: 'normal',
        contentDensity: 'compact',
    },
    {
        value: 'layered_left',
        name: 'Layered Left',
        description: 'Soft layered header with profile anchored left.',
        profilePlacement: 'left',
        headerVariant: 'layered',
        headerHeight: 'normal',
        contentDensity: 'spacious',
    },
    {
        value: 'layered_right',
        name: 'Layered Right',
        description: 'Soft layered header with profile anchored right.',
        profilePlacement: 'right',
        headerVariant: 'layered',
        headerHeight: 'normal',
        contentDensity: 'spacious',
    },
    {
        value: 'minimal_center',
        name: 'Minimal Center',
        description: 'Compact clean header and centered minimalist profile.',
        profilePlacement: 'center',
        headerVariant: 'clean',
        headerHeight: 'compact',
        contentDensity: 'compact',
    },
];

const styleMap = Object.fromEntries(styleTemplates.map((template) => [template.value, template]));

const activeTemplate = computed(() => ({
    headerHeight: 'normal',
    contentDensity: 'normal',
    ...(styleMap[state.templateStyle] ?? styleMap.classic),
}));
const isWaveTemplate = computed(() => activeTemplate.value.headerVariant === 'wave');
const showWaveDecoration = computed(() => isWaveTemplate.value && !state.headerImagePreview);
const waveDecorationStyle = computed(() => ({ color: state.headerColor }));

const headerShapeClass = computed(() => {
    if (activeTemplate.value.headerVariant === 'wave') {
        return 'relative overflow-hidden rounded-b-[2.2rem]';
    }

    if (activeTemplate.value.headerVariant === 'layered') {
        return 'relative overflow-hidden rounded-b-[2rem]';
    }

    if (activeTemplate.value.headerVariant === 'diagonal') {
        return 'relative overflow-hidden [clip-path:polygon(0_0,100%_0,100%_86%,0_100%)]';
    }

    return 'relative';
});

const headerHeightClass = computed(() => {
    if (activeTemplate.value.headerHeight === 'tall') {
        return 'h-28';
    }

    if (activeTemplate.value.headerHeight === 'compact') {
        return 'h-20';
    }

    return 'h-24';
});

const previewBodyContainerClass = computed(() => {
    if (activeTemplate.value.contentDensity === 'spacious') {
        return '-mt-9 px-4 pb-5';
    }

    if (activeTemplate.value.contentDensity === 'compact') {
        return '-mt-7 px-4 pb-3';
    }

    return '-mt-8 px-4 pb-4';
});

const profileWrapClass = computed(() => {
    if (activeTemplate.value.profilePlacement === 'left') {
        return 'flex justify-start';
    }

    if (activeTemplate.value.profilePlacement === 'right') {
        return 'flex justify-end';
    }

    return 'flex justify-center';
});

const profileImageClass = computed(() => 'relative z-30 h-16 w-16 object-cover shadow-[0_12px_26px_rgba(15,23,42,0.32)]');

const textAlignClass = computed(() => {
    if (activeTemplate.value.profilePlacement === 'left') {
        return 'text-left';
    }

    if (activeTemplate.value.profilePlacement === 'right') {
        return 'text-right';
    }

    return 'text-center';
});

const textBlockClass = computed(() => 'mt-3');

const cropConfig = {
    profile: { width: 800, height: 800 },
    header: { width: 1500, height: 500 },
};

const cropModalTitle = computed(() => (
    cropTarget.value === 'header' ? copy.value.cropHeaderImageTitle : copy.value.cropProfileImageTitle
));

const cropPreviewClass = computed(() => (
    cropTarget.value === 'header' ? 'aspect-[3/1] w-full' : 'h-64 w-64'
));

const triggerInput = (type) => {
    if (type === 'profile') {
        profileInputRef.value?.click();
        return;
    }

    if (type === 'header') {
        headerInputRef.value?.click();
        return;
    }

    backgroundInputRef.value?.click();
};

const fileToDataUrl = (file) =>
    new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target?.result || '');
        reader.readAsDataURL(file);
    });

const onProfileSelected = async (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    cropTarget.value = 'profile';
    originalImageFile.value = file;
    cropSource.value = await fileToDataUrl(file);
    cropZoom.value = 1;
    cropOffsetX.value = 0;
    cropOffsetY.value = 0;
    showCropModal.value = true;
    event.target.value = '';
};

const onHeaderSelected = async (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    cropTarget.value = 'header';
    originalImageFile.value = file;
    cropSource.value = await fileToDataUrl(file);
    cropZoom.value = 1;
    cropOffsetX.value = 0;
    cropOffsetY.value = 0;
    showCropModal.value = true;
    event.target.value = '';
};

const onBackgroundSelected = async (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    backgroundImageFile.value = file;
    state.backgroundImagePreview = await fileToDataUrl(file);
    event.target.value = '';
};

const closeCropModal = () => {
    showCropModal.value = false;
    cropSource.value = '';
    originalImageFile.value = null;
};

const applyCrop = () => {
    if (!cropSource.value || !originalImageFile.value) {
        closeCropModal();
        return;
    }

    const img = new Image();
    img.onload = () => {
        const config = cropConfig[cropTarget.value] ?? cropConfig.profile;
        const canvas = document.createElement('canvas');
        canvas.width = config.width;
        canvas.height = config.height;

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            closeCropModal();
            return;
        }

        const targetAspect = config.width / config.height;
        const imageAspect = img.width / img.height;

        let baseWidth = img.width;
        let baseHeight = img.height;

        if (imageAspect > targetAspect) {
            baseWidth = img.height * targetAspect;
        } else {
            baseHeight = img.width / targetAspect;
        }

        const cropWidth = baseWidth / cropZoom.value;
        const cropHeight = baseHeight / cropZoom.value;

        const maxShiftX = Math.max(0, (img.width - cropWidth) / 2);
        const maxShiftY = Math.max(0, (img.height - cropHeight) / 2);
        const centerX = (img.width / 2) + ((cropOffsetX.value / 100) * maxShiftX);
        const centerY = (img.height / 2) + ((cropOffsetY.value / 100) * maxShiftY);

        let sx = centerX - (cropWidth / 2);
        let sy = centerY - (cropHeight / 2);

        sx = Math.max(0, Math.min(sx, img.width - cropWidth));
        sy = Math.max(0, Math.min(sy, img.height - cropHeight));

        ctx.drawImage(img, sx, sy, cropWidth, cropHeight, 0, 0, config.width, config.height);

        canvas.toBlob((blob) => {
            if (!blob) {
                closeCropModal();
                return;
            }

            const croppedFile = new File([blob], originalImageFile.value.name, {
                type: 'image/jpeg',
            });

            if (cropTarget.value === 'header') {
                headerImageFile.value = croppedFile;
                state.headerImagePreview = URL.createObjectURL(croppedFile);
            } else {
                profileImageFile.value = croppedFile;
                state.profileImagePreview = URL.createObjectURL(croppedFile);
            }

            closeCropModal();
        }, 'image/jpeg', 0.92);
    };

    img.src = cropSource.value;
};

const addLink = () => {
    if (state.links.length >= 20) {
        return;
    }

    const newLink = createLinkItem();
    state.links.push(newLink);
    openLinkUid.value = newLink._uid;
};

const removeLink = (index) => {
    const [removed] = state.links.splice(index, 1);

    if (openLinkUid.value === removed?._uid) {
        openLinkUid.value = state.links[index]?._uid ?? state.links[index - 1]?._uid ?? null;
    }
};

const getLinkIconOption = (value) => linkIconMap[value] ?? defaultLinkIcon;

const toggleLinkDetails = (uid) => {
    openLinkUid.value = openLinkUid.value === uid ? null : uid;
};

const onLinkDragStart = (index) => {
    draggingLinkIndex.value = index;
};

const onLinkDrop = (index) => {
    if (draggingLinkIndex.value === null || draggingLinkIndex.value === index) {
        draggingLinkIndex.value = null;
        return;
    }

    const [moved] = state.links.splice(draggingLinkIndex.value, 1);
    state.links.splice(index, 0, moved);
    draggingLinkIndex.value = null;
};

const clearLinkDragState = () => {
    draggingLinkIndex.value = null;
};

const saveLinks = () => {
    const sanitizedLinks = state.links
        .map((link) => ({
            icon: (link.icon ?? 'link').toString(),
            title: (link.title ?? '').trim(),
            url: (link.url ?? '').trim(),
            description: (link.description ?? '').trim(),
            auto_key: (link.auto_key ?? '').toString().trim(),
        }))
        .filter((link) => link.title || link.url);

    linksForm
        .transform(() => ({
            links: sanitizedLinks,
        }))
        .put(`/cards/${props.card.id}`, {
            preserveScroll: true,
            preserveState: true,
        });
};

const addSchedule = () => {
    if (state.schedule.length >= 7) {
        return;
    }

    const existingDays = new Set(state.schedule.map(s => s.day));
    let nextDay = 1; // Start with Monday

    for (let i = 0; i < 7; i++) {
        if (!existingDays.has(i)) {
            nextDay = i;
            break;
        }
    }

    state.schedule.push({
        day: nextDay,
        open: '09:00',
        close: '17:00',
    });

    // Sort by day (Sunday to Saturday)
    state.schedule.sort((a, b) => a.day - b.day);
};

const removeSchedule = (index) => {
    state.schedule.splice(index, 1);
};

const saveSchedule = () => {
    // Remove duplicates and sort
    const uniqueSchedule = [];
    const seenDays = new Set();

    for (const item of state.schedule) {
        if (!seenDays.has(item.day)) {
            seenDays.add(item.day);
            uniqueSchedule.push({
                day: item.day,
                open: item.open,
                close: item.close,
            });
        }
    }

    // Sort by day
    uniqueSchedule.sort((a, b) => a.day - b.day);

    scheduleForm
        .transform(() => ({
            schedule: uniqueSchedule,
        }))
        .put(`/cards/${props.card.id}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                state.schedule = uniqueSchedule;
            },
        });
};

const saveBasicInfo = () => {
    basicForm
        .transform(() => ({
            name: state.name,
            username: state.username,
            description: state.description,
            phone: state.phone,
            email: state.email,
            address: state.address,
            google_maps_url: state.googleMapsUrl,
        }))
        .put(`/cards/${props.card.id}`, {
            preserveScroll: true,
            preserveState: true,
        });
};

const saveColors = () => {
    colorsForm
        .transform(() => ({
            header_color: state.headerColor,
            text_color: state.textColor,
            button_background_color: state.buttonBackgroundColor,
            button_text_color: state.buttonTextColor,
            background_color: state.backgroundColor,
        }))
        .put(`/cards/${props.card.id}`, {
            preserveScroll: true,
            preserveState: true,
        });
};

const saveStyleTemplate = () => {
    stylesForm
        .transform(() => ({
            template_style: state.templateStyle,
        }))
        .put(`/cards/${props.card.id}`, {
            preserveScroll: true,
            preserveState: true,
        });
};

const saveButtonStyle = () => {
    buttonStyleForm
        .transform(() => ({
            button_style: state.buttonStyle,
        }))
        .put(`/cards/${props.card.id}`, {
            preserveScroll: true,
            preserveState: true,
        });
};

const saveImages = () => {
    imagesForm
        .transform(() => ({
            _method: 'put',
            profile_image_style: state.profileImageStyle,
            remove_profile_image: false,
            remove_header_image: false,
            remove_background_image: false,
            profile_image: profileImageFile.value,
            header_image: headerImageFile.value,
            background_image: backgroundImageFile.value,
        }))
        .post(`/cards/${props.card.id}`, {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                profileImageFile.value = null;
                headerImageFile.value = null;
                backgroundImageFile.value = null;
                imagesForm.remove_profile_image = false;
                imagesForm.remove_header_image = false;
                imagesForm.remove_background_image = false;
            },
        });
};

const openRemoveProfileModal = () => {
    showRemoveProfileModal.value = true;
};

const closeRemoveProfileModal = () => {
    showRemoveProfileModal.value = false;
};

const confirmRemoveProfileImage = () => {
    imagesForm
        .transform(() => ({
            _method: 'put',
            profile_image_style: state.profileImageStyle,
            remove_profile_image: true,
        }))
        .post(`/cards/${props.card.id}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                closeRemoveProfileModal();
                state.profileImagePreview = '';
                profileImageFile.value = null;
                imagesForm.remove_profile_image = false;
            },
        });
};

const openRemoveHeaderModal = () => {
    showRemoveHeaderModal.value = true;
};

const closeRemoveHeaderModal = () => {
    showRemoveHeaderModal.value = false;
};

const confirmRemoveHeaderImage = () => {
    imagesForm
        .transform(() => ({
            _method: 'put',
            profile_image_style: state.profileImageStyle,
            remove_header_image: true,
        }))
        .post(`/cards/${props.card.id}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                closeRemoveHeaderModal();
                state.headerImagePreview = '';
                headerImageFile.value = null;
                imagesForm.remove_header_image = false;
            },
        });
};

const openRemoveBackgroundModal = () => {
    showRemoveBackgroundModal.value = true;
};

const closeRemoveBackgroundModal = () => {
    showRemoveBackgroundModal.value = false;
};

const confirmRemoveBackgroundImage = () => {
    imagesForm
        .transform(() => ({
            _method: 'put',
            profile_image_style: state.profileImageStyle,
            remove_background_image: true,
        }))
        .post(`/cards/${props.card.id}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                closeRemoveBackgroundModal();
                state.backgroundImagePreview = '';
                backgroundImageFile.value = null;
                imagesForm.remove_background_image = false;
            },
        });
};

const copyPublicCardUrl = async () => {
    const value = publicCardUrl.value;
    if (!value) {
        return;
    }

    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(value);
        } else {
            const input = document.createElement('input');
            input.value = value;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
        }

        copiedPublicUrl.value = true;
        window.setTimeout(() => {
            copiedPublicUrl.value = false;
        }, 1600);
    } catch {
        copiedPublicUrl.value = false;
    }
};

watch(activeTab, (tab) => {
    if (typeof window === 'undefined') {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.replaceState({}, '', url);
});
</script>

<style scoped>
.studio-scroll::-webkit-scrollbar {
    width: 5px;
    height: 5px;
}

.studio-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.studio-scroll::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.06);
    border-radius: 9999px;
}

.studio-scroll:hover::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.16);
}

.studio-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(15, 23, 42, 0.14) transparent;
}

.upload-mask {
    border: 1px dashed rgb(203 213 225);
    border-radius: 0.9rem;
    background: linear-gradient(180deg, #ffffff, #f8fafc);
    overflow: hidden;
    transition: border-color 0.2s ease, transform 0.2s ease;
}

.upload-mask:hover {
    border-color: #6DBE45;
    transform: translateY(-1px);
}
</style>
