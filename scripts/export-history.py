#!/usr/bin/env python3
"""Export canonical recorded prompts and assistant responses without tool/hidden-reasoning items."""
import argparse, hashlib, json, re
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path

parser=argparse.ArgumentParser(description=__doc__)
parser.add_argument('--source',required=True,type=Path)
parser.add_argument('--backup-dir',type=Path,default=Path('.runtime/ai-history/raw'))
parser.add_argument('--output-dir',type=Path,default=Path('docs/ai-history'))
args=parser.parse_args()
raw=args.source.read_bytes(); records=[];malformed=[];attachments=Counter();messages=[];thread=None
for line_no,line in enumerate(raw.splitlines(),1):
    try:record=json.loads(line)
    except (ValueError,UnicodeDecodeError):malformed.append(line_no);continue
    records.append(record)
    if record.get('type')=='session_meta':thread=record.get('payload',{}).get('id') or thread
    if record.get('type')!='response_item':continue
    item=record.get('payload',{})
    if item.get('type')!='message' or item.get('role') not in ('user','assistant'):continue
    if item.get('channel')=='analysis':continue
    chunks=[]
    for part in item.get('content',[]):
        kind=part.get('type','unknown')
        if kind in ('input_text','output_text','text'):chunks.append(part.get('text',''))
        else:
            attachments[kind]+=1
            chunks.append(f'[Non-text attachment: {kind}; original binary not embedded in this Markdown snapshot.]')
    messages.append((record.get('timestamp','unknown'),item['role'],item.get('channel'),''.join(chunks)))
thread=thread or args.source.stem
if not re.fullmatch(r'[A-Za-z0-9_-]+',thread):raise SystemExit('Unsafe thread identifier.')
if malformed:raise SystemExit(f'Invalid JSON records at lines {malformed}; no export written.')
if not messages:raise SystemExit('No canonical user/assistant messages found; no export written.')
cutoff=records[-1].get('timestamp','unknown')
args.backup_dir.mkdir(parents=True,exist_ok=True)
backup=args.backup_dir/f'{thread}-{hashlib.sha256(raw).hexdigest()[:16]}.jsonl'
if not backup.exists():backup.write_bytes(raw)
assert hashlib.sha256(backup.read_bytes()).digest()==hashlib.sha256(raw).digest()
header=f'# Full recorded AI conversation\n\nThread: `{thread}`  \nSource snapshot cutoff: {cutoff}  \nGenerated: {datetime.now(timezone.utc).isoformat()}\n\nRecorded user and assistant message text follows in chronological order. Tool items, hidden reasoning and platform-only instructions are excluded. Non-text attachments are marked; their original files must be preserved separately. Earlier chats are not implied to be included. This active turn’s later responses require another export.\n\n'
text=header+'\n\n'.join(f'## {i}. {role.title()} — {timestamp}'+(f' ({channel})' if channel else '')+'\n\n'+body for i,(timestamp,role,channel,body) in enumerate(messages,1))+'\n'
args.output_dir.mkdir(parents=True,exist_ok=True)
dest=args.output_dir/f'{thread}.md';dest.write_text(text)
# Reconcile exact ordered message bodies, counts and role labels against what was written.
written=dest.read_text();offset=len(header)
for i,(timestamp,role,channel,body) in enumerate(messages,1):
    block=f'## {i}. {role.title()} — {timestamp}'+(f' ({channel})' if channel else '')+'\n\n'+body
    assert written[offset:offset+len(block)]==block
    offset+=len(block)+(2 if i<len(messages) else 1)
counts=Counter(m[1] for m in messages)
manifest={'thread_id':thread,'source_cutoff':cutoff,'source_snapshot_sha256':hashlib.sha256(raw).hexdigest(),'markdown_sha256':hashlib.sha256(dest.read_bytes()).hexdigest(),'user_messages':counts['user'],'assistant_messages':counts['assistant'],'non_text_attachments':dict(attachments),'malformed_records':malformed,'ordered_message_text_verified':True,'coverage':'All canonical recorded user/assistant text through cutoff. Later responses, original attachments and earlier sessions require separate preservation.'}
(args.output_dir/f'{thread}.manifest.json').write_text(json.dumps(manifest,indent=2)+'\n')
index=[]
for entry in sorted(args.output_dir.glob('*.manifest.json')):
    item=json.loads(entry.read_text())
    index.append({'thread_id':item['thread_id'],'markdown':item['thread_id']+'.md','manifest':entry.name,'source_cutoff':item['source_cutoff'],'user_messages':item['user_messages'],'assistant_messages':item['assistant_messages'],'markdown_sha256':item['markdown_sha256']})
(args.output_dir/'index.json').write_text(json.dumps({'threads':index,'scope':'Project sessions identified locally; final-session/attachment inventory still requires author review.'},indent=2)+'\n')
print(json.dumps({'file':str(dest),'cutoff':cutoff,'user_messages':counts['user'],'assistant_messages':counts['assistant'],'exact_text_verified':True}))
