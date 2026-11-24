// 検索バーの下に候補を出す（すべての検索バーで共通）
// 使い方: <input data-search-suggest="communities" data-search-suggest-url="/search/suggest"> を検索のフォームの中に置く
//   種類: all（全体検索）/ communities / events / helps
// Enter は今までどおりフォームを送って一覧を絞り込む。候補を矢印キーで選んで Enter、またはクリックするとそのページを開く

const DELAY = 200;
const ICONS = {
    community: 'fa-people-group',
    event: 'fa-calendar-days',
    help: 'fa-hand-holding-heart',
    user: 'fa-user',
};

let uid = 0;

const setup = (input) => {
    if (input.dataset.searchSuggestReady) {
        return;
    }
    input.dataset.searchSuggestReady = '1';

    const form = input.form;
    const url = input.dataset.searchSuggestUrl;
    const type = input.dataset.searchSuggest || 'all';
    const anchor = input.closest('[data-search-suggest-anchor]') || input.parentElement;
    const listId = `search-suggest-${++uid}`;

    anchor.classList.add('search-suggest-anchor');
    const panel = document.createElement('div');
    panel.className = 'search-suggest';
    panel.id = listId;
    panel.setAttribute('role', 'listbox');
    panel.hidden = true;
    anchor.append(panel);

    input.setAttribute('autocomplete', 'off');
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-controls', listId);
    input.setAttribute('aria-expanded', 'false');

    let timer = null;
    let controller = null;
    let items = [];
    let active = -1;

    const close = () => {
        panel.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        active = -1;
    };

    const setActive = (index) => {
        const options = panel.querySelectorAll('.search-suggest-item');
        options.forEach((option, i) => option.classList.toggle('is-active', i === index));
        active = index;
        if (index >= 0 && options[index]) {
            input.setAttribute('aria-activedescendant', options[index].id);
            options[index].scrollIntoView({ block: 'nearest' });
        } else {
            input.removeAttribute('aria-activedescendant');
        }
    };

    // 入力した言葉に当たる部分を太字にする（文字は textContent で入れるので HTML として扱わない）
    const highlight = (element, text, keyword) => {
        const words = keyword.trim().split(/\s+/).filter(Boolean).map((word) => word.toLowerCase());
        const lower = text.toLowerCase();
        let index = 0;
        while (index < text.length) {
            let next = -1;
            let length = 0;
            words.forEach((word) => {
                const found = lower.indexOf(word, index);
                if (found !== -1 && (next === -1 || found < next)) {
                    next = found;
                    length = word.length;
                }
            });
            if (next === -1) {
                element.append(text.slice(index));
                break;
            }
            element.append(text.slice(index, next));
            const mark = document.createElement('mark');
            mark.textContent = text.slice(next, next + length);
            element.append(mark);
            index = next + length;
        }
    };

    const render = (keyword) => {
        panel.replaceChildren();
        items.forEach((item, i) => {
            const link = document.createElement('a');
            link.className = 'search-suggest-item';
            link.id = `${listId}-${i}`;
            link.href = item.url;
            link.setAttribute('role', 'option');

            const icon = document.createElement('span');
            icon.className = `search-suggest-icon search-suggest-icon--${item.type}`;
            icon.innerHTML = `<i class="fa-solid ${ICONS[item.type] || 'fa-magnifying-glass'}" aria-hidden="true"></i>`;

            const text = document.createElement('span');
            text.className = 'search-suggest-text';
            const title = document.createElement('span');
            title.className = 'search-suggest-title';
            highlight(title, item.title, keyword);
            text.append(title);
            if (item.subtitle) {
                const subtitle = document.createElement('span');
                subtitle.className = 'search-suggest-subtitle';
                subtitle.textContent = item.subtitle;
                text.append(subtitle);
            }

            const label = document.createElement('span');
            label.className = 'search-suggest-type';
            label.textContent = item.type_label;

            link.append(icon, text, label);
            panel.append(link);
        });

        if (items.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'search-suggest-empty';
            empty.textContent = input.dataset.searchSuggestEmptyText || 'No matches';
            panel.append(empty);
        }

        // 一覧で全部の結果を見る（フォームを送る）
        const all = document.createElement('button');
        all.type = 'submit';
        all.className = 'search-suggest-all';
        all.textContent = `${input.dataset.searchSuggestAllText || 'See all results for'} "${keyword}"`;
        panel.append(all);

        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        setActive(-1);
    };

    const load = async () => {
        const keyword = input.value.trim();
        if (keyword === '') {
            close();
            return;
        }
        controller?.abort();
        controller = new AbortController();
        try {
            const query = new URLSearchParams({ type, search: keyword });
            const response = await fetch(`${url}?${query}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error();
            }
            items = (await response.json()).items || [];
            // 待っている間に入力が変わっていたら、古い結果は出さない
            if (input.value.trim() === keyword && document.activeElement === input) {
                render(keyword);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                close();
            }
        }
    };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(load, DELAY);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim() !== '') {
            load();
        }
    });

    input.addEventListener('keydown', (event) => {
        if (panel.hidden) {
            return;
        }
        const count = panel.querySelectorAll('.search-suggest-item').length;
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActive(count === 0 ? -1 : (active + 1) % count);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActive(count === 0 ? -1 : (active <= 0 ? count - 1 : active - 1));
        } else if (event.key === 'Enter' && active >= 0) {
            // 候補を選んでいるときは、そのページを開く（選んでいないときは今までどおり一覧を絞り込む）
            event.preventDefault();
            window.location.href = items[active].url;
        } else if (event.key === 'Escape') {
            close();
        }
    });

    // 候補の外を押したら閉じる
    document.addEventListener('click', (event) => {
        if (!anchor.contains(event.target)) {
            close();
        }
    });

    form?.addEventListener('submit', close);
};

export const initSearchSuggest = (root = document) => {
    root.querySelectorAll('input[data-search-suggest]').forEach(setup);
};

initSearchSuggest();
