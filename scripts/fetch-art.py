"""Download public Steam promotional artwork once for the demo (not at request time)."""
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path
import json, urllib.request
ids=[1245620,1091500,990080,1174180,1086940,1593500,1551360,1145360,367520,413150,271590,292030,275850,782330,814380,2050650,1426210,620,105600,289070,1466860,255710,227300,1172620,1240440,1850570,632470,588650,504230]
root=Path('public/images/games');root.mkdir(parents=True,exist_ok=True)
def fetch(app):
    try:
        cover=root/f'{app}.jpg'
        if not cover.exists(): urllib.request.urlretrieve(f'https://cdn.akamai.steamstatic.com/steam/apps/{app}/library_600x900.jpg',cover)
        data=json.load(urllib.request.urlopen(f'https://store.steampowered.com/api/appdetails?appids={app}',timeout=25))
        for i,scene in enumerate(data[str(app)]['data'].get('screenshots',[])[:3],1):
            dest=root/f'{app}-scene-{i}.jpg'
            if not dest.exists():urllib.request.urlretrieve(scene['path_full'],dest)
        return f'{app}: ready'
    except Exception as exc:return f'{app}: {exc}'
with ThreadPoolExecutor(max_workers=6) as pool:
    for result in pool.map(fetch,ids): print(result,flush=True)
