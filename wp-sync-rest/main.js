const urls = document.getElementById('urls');
const url = document.getElementById('url');
const sync_post = document.getElementById('sync_posts');
const sync_form = document.getElementById('sync_form');
const api_key = document.getElementById('api_key');
const syncing = document.getElementById('syncing');
const listItems = document.querySelectorAll('#urls > li');
const sync_all = document.getElementById('sync_all');
const sync_pages = document.getElementById('sync_pages');
const empty_url = document.getElementById('empty_url');
const empty_key = document.getElementById('empty_key');
const enterSite = document.getElementById('enterSite');
const emptySitesErr = document.getElementById('emptySitesErr');
const clear = document.getElementById('clear');
const heading = document.getElementById('heading');
const customPostSync = document.getElementById('customPostSync');
const catSync = document.getElementById('catSync');
let url_list = [];
let settingsApplied = {postType : 'any' , category  : 'all', taxonomy : 'all', size : '-1'};
const postTypeInp = document.getElementById('postTypeInp');
const categoryInp = document.getElementById('categoryInp');
const sync_Page_Enter = document.getElementById('sync_Page_Enter');
const selectPostType = document.getElementById('selectPostType');
const selectCategory = document.getElementById('selectCategory');
const paginationSize = document.getElementById('size');
const selectedTaxonomy = document.getElementById('selectTaxonomy');
const applySettings = document.getElementById('applySettings');
const emptyHeading = document.getElementById('emptyHeading');
const wooSync = document.getElementById('wooSync');
const wooData = document.getElementById('wooData');
const response = document.getElementById('response');
if(applySettings){
applySettings.addEventListener('click',()=>{
    settingsApplied.postType = selectPostType.options[selectPostType.selectedIndex].text.toLowerCase();
    settingsApplied.category = selectCategory.value;
    settingsApplied.taxonomy = selectedTaxonomy.value;
    settingsApplied.size = paginationSize.value
    console.log("Applied", settingsApplied.postType , settingsApplied.category , settingsApplied.taxonomy , settingsApplied.size)
})
}

document.addEventListener('DOMContentLoaded', function() {
(async function init() {
try{
    const res = await fetch(`${syncData.home_url}/wp-json/sync-api/v1/getwebsitedata`, {
    method: "GET",
    headers: { "Content-Type": "application/json" }
    });
    const data = await res.json()
    console.log('Data :' , data)
    data.forEach((val,indx)=>{
    url_list[indx] = data[indx]
    })
    url_list.forEach((val,indx)=>{
            const li = document.createElement('li');
    li.innerHTML = `<p class="urlLink">${val[1]}</p><p class="apiKeyVal">${val[0]}</p> <button class="deleteButton" id="deleteButton" type="button" title="Delete" >&times;</button> `
    urls.append(li);
    })

    if(emptyHeading){
if(url_list.length === 0){
    emptyHeading.classList.remove('hide');
}
}
    if(emptyHeading){
if(url_list.length != 0){
    sync_Page_Enter.classList.remove('hide');
    wooData.classList.remove('hide');
}
}
}
catch(e){
   console.log(e);
}
})();
})

clear.addEventListener('click',()=>{
        url_list = [];
    urls.innerHTML = "";
})

    urls.addEventListener("click",async (e) => {
        if (!e.target.classList.contains("deleteButton")) return;
        let li = e.target.closest("li");
            let url = li.querySelector(".urlLink").textContent.trim();
    let key = li.querySelector(".apiKeyVal").textContent.trim();
        url_list = url_list.filter(
            item => item[0] !== key && item[1] !== url
        );
        li.remove();
        try{
        res = await fetch(`${syncData.home_url}/wp-json/sync-api/v1/deletesite`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                site_key: key,
                site_url: url
            })
        });
        }catch(e){
    console.log(e);
}
    });
    
async function syncWooData(index){
    console.log("Process started")
    if(url_list.length === 0) return;
    syncing.classList.remove('dx-none');
    let site = syncData.home_url;
    let senderUrl = `${site}/wp-json/sync-api/v1/syncWooData/products/get`;
    let userData = {key:  syncData.api_key};
    let res = '';
   try{ 
    res = await fetch(senderUrl, {
        method: 'POST',
        body: JSON.stringify(userData),
        headers: { 'Content-Type': 'application/json' }
    });
      if(!res.ok){
        throw new Error("Error ocurred");
       }
      }catch(e){
        throw new Error("Error ocurred");
      }
    let all_products = await res.json();
    Object.values(all_products).forEach(element => {
        console.log("Products:", element);
    });
  // ----------------
      site = url_list[index][1];
      userData = {
        key:  url_list[index][0],              
        method : 'post',
        all_products: all_products,
    };

    let resUrl = `${site}/wp-json/sync-api/v1/syncWooData/products/set`;
try{
    console.log(`Process started for Post`)
    let res = await fetch(resUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(userData)
    });
    if(!res.ok){
        throw new Error("Error ocurred");
    }
    let responseText = await res.json();
    response.innerHTML = `<p>${responseText.Response}</p>`;
    response.classList.remove('hide');
}catch(e){
    throw new Error("Error ocurred");
}

// ---------------------- 
    console.log("Process started")
    if(url_list.length === 0) return;
    syncing.classList.remove('dx-none');
    site = syncData.home_url;
    senderUrl = `${site}/wp-json/sync-api/v1/syncWooData/orders/get`;
    userData = {key:  syncData.api_key};
    res = '';
   try{ 
    res = await fetch(senderUrl, {
        method: 'POST',
        body: JSON.stringify(userData),
        headers: { 'Content-Type': 'application/json' }
    });
      if(!res.ok){
        throw new Error("Error ocurred");
       }
      }catch(e){
        throw new Error("Error ocurred");
      }
    let all_orders = await res.json();
    Object.values(all_orders).forEach(element => {
        console.log("Orders:", element);
    });
    //-------------------
    site = url_list[index][1];
    userData = {
        key:  url_list[index][0],              
        method : 'post',
        all_orders: all_orders,
    };

    resUrl = `${site}/wp-json/sync-api/v1/syncWooData/orders/set`;
try{
    console.log(`Process started for setting orders`)
    let res = await fetch(resUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(userData)
    });
    if(!res.ok){
        throw new Error("Error ocurred");
    }
    let responseText = await res.json();
    let para = response.appendChild(document.createElement("p"));
    para.innerText = responseText.Response;
    response.classList.remove('hide');
}catch(e){
    throw new Error("Error ocurred");
}

}
if(wooSync){
    wooSync.addEventListener('click', async () => {
        if(url_list.length === 0){
        return
    }
        syncing.classList.remove('dx-none');
        try{
            let isNotComplete = 0;
        for (let i = 0; i < url_list.length; i++) {
            syncing.textContent = `Syncing site: ${url_list[i][1]}`;
            try{
                await syncWooData(i);
                const li = document.querySelectorAll('#urls > li')[i];
                if (!li.querySelector('.status')) {
                const span = document.createElement('span');
                span.className = 'status';
                span.textContent = 'SUCCESS';
                span.classList.remove('failure');
                span.classList.add('success');
                li.appendChild(span);
                } else {
                li.querySelector('.status').textContent = 'SUCCESS';
                        }
            }catch(e){
                // throw new Error(e.message);
                const li = document.querySelectorAll('#urls > li')[i];
                if (!li.querySelector('.status')) {
                const span = document.createElement('span');
                span.className = 'status';
                span.textContent = ' FAILURE';
                span.classList.remove('success');
                span.classList.add('failure');
                li.appendChild(span);
                } else {
                li.querySelector('.status').textContent = ' FAILURE';
                        }

            isNotComplete = 1;
            }
        }
        syncing.textContent = "Syncing Completed";
    }catch(e){
            syncing.style.background = "red";
            syncing.textContent = "Error Occured";
    }

        setTimeout(() => {
            syncing.classList.add('dx-none');
            syncing.style.background = "#005c8b";
        }, 1000);
    });
}
if(sync_Page_Enter){
sync_Page_Enter.addEventListener('click', async () => {
    if(url_list.length === 0){
    return
}
    syncing.classList.remove('dx-none');
    try{
        let isNotComplete = 0;
    for (let i = 0; i < url_list.length; i++) {
        syncing.textContent = `Syncing site: ${url_list[i][1]}`;
        try{
            await syncDataFunc(i, settingsApplied.postType , settingsApplied.category , settingsApplied.taxonomy,  settingsApplied.size);
            const li = document.querySelectorAll('#urls > li')[i];
            if (!li.querySelector('.status')) {
             const span = document.createElement('span');
             span.className = 'status';
             span.textContent = 'SUCCESS';
             span.classList.remove('failure');
             span.classList.add('success');
             li.appendChild(span);
            } else {
             li.querySelector('.status').textContent = 'SUCCESS';
                    }
        }catch(e){
            const li = document.querySelectorAll('#urls > li')[i];
            if (!li.querySelector('.status')) {
             const span = document.createElement('span');
             span.className = 'status';
             span.textContent = ' FAILURE';
             span.classList.remove('success');
             span.classList.add('failure');
             li.appendChild(span);
            } else {
             li.querySelector('.status').textContent = ' FAILURE';
                    }

        isNotComplete = 1;
        }
    }
    syncing.textContent = "Syncing Completed";
}catch(e){
        syncing.style.background = "red";
        syncing.textContent = "Error Occured";
}

    setTimeout(() => {
        syncing.classList.add('dx-none');
        syncing.style.background = "#005c8b";
    }, 1000);
});
}
if(enterSite){
enterSite.addEventListener('click', async (e)=>{
    emptySitesErr.classList.add('hide');
    clear.classList.remove('hide');
    heading.classList.remove('hide');
    // if(e.key === 'Enter'){
    if(api_key.value.trim() ==='' || url.value.trim() === ''){
    empty_url.classList.add('hide');
    if(url.value.trim() === ''){
    empty_url.classList.remove('hide');
    }
    empty_key.classList.add('hide');
    if(api_key.value.trim() ===''){
    empty_key.classList.remove('hide');
    }
    return
}
let duplicate = url_list.some(val => 
    api_key.value.trim() === val[0] && url.value.trim() === val[1]
);

if (duplicate) {
    url.value = '';
    api_key.value = '';
    console.log("Duplicate found!");
    return;
}
try{
res = await fetch(`${syncData.home_url}/wp-json/sync-api/v1/setwebsitedata`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
        site_key: api_key.value.trim(),
        site_url: url.value.trim()
    })
});
let data = await res.json();
console.log(`Send Data Res : ${data.success} , ${data.message}`)
}catch(e){
    console.log(e);
}

    const li = document.createElement('li');
    empty_url.classList.add('hide');
    empty_key.classList.add('hide');
    li.textContent  = url.value.trim();
    api_key_val = api_key.value.trim();
    url_list.push([api_key_val , li.textContent ]);
    li.dataset.url = li.textContent;
    li.dataset.key = api_key_val;
    li.innerHTML = `<p class="urlLink">${li.textContent}</p><p class="apiKeyVal">${api_key_val}</p> <button class="deleteButton" type="button"  >&times;</button> `
    urls.append(li);
    url.value = '';
    api_key.value = '';
});
}
const key = document.getElementById('your_key');
key.addEventListener('click',()=>{
    key.textContent = syncData.api_key ;
});


async function syncDataFunc(index , postType = 'any' , category = 'all' ,taxonomy = 'all' , size = 10) {
   if(category === "all categories"){
    category = 'all'
   }
   if(postType === "all posts"){
    postType = 'all'
   }
    let resp1;
    let site = url_list[index][1];
    let userData = {
        key:  url_list[index][0],              
        postType: postType,
        category: category,
        size: size,
        taxonomy_name : taxonomy,
        myallpostsItems: syncData.myallpostsItems
    };

    let resUrl = `${site}/wp-json/sync-api/v1/syncData`;
try{
    let res = await fetch(resUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(userData)
    });
    if(!res.ok){
        throw new Error("Error ocurred");
    }
    resp1 = await res.json();
    console.log("Response from B:", resp1);
    console.log("Message  :\n", resp1.message);

}catch(e){
    console.log(`Error : ${e.message} `);
}
    let B_posts = resp1.posts; 
  
    site = syncData.home_url;

    userData = {
        key: syncData.api_key,
        postType: postType,
        category: category,
        size: size,
        taxonomy_name : taxonomy,
        myallpostsItems: B_posts 
    };

    resUrl = `${site}/wp-json/sync-api/v1/syncData`;  
try{
    res = await fetch(resUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(userData)
    });
        if(!res.ok){
        throw new Error("Error ocurred");
    }
    let resp2 = await res.json();
    console.log("Response from A:", resp2);
}catch(e){
    console.log(`Error : ${e.message} `);
}
}
